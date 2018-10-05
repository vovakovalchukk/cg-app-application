<?php
namespace CG\CourierAdapter\Provider\Implementation\Storage;

use CG\CourierAdapter\StorageInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LoggerInterface;
use CG\Stdlib\Log\LogTrait;
use Predis\Client as PredisClient;
use CG\Predis\Command\Setnxex;
use CG\CourierAdapter\ShipmentInterface as Shipment;
use CG\CourierAdapter\Exception\UserError;

class Redis implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'CourierAdapterRedis';
    const KEY_PREFIX = 'CourierAdapter:';

    const CACHE_PARCEL_NUMBER_LOCK_KEY_PREFIX = 'ParcelNumberLock';

    const LOCK_EXPIRY_SECONDS = 1;
    const LOCK_RETRY_WAIT_MICROSECONDS = 200000;
    const LOCK_MAX_RETRIES = 5;

    /** @var PredisClient */
    protected $predisClient;

    public function __construct(PredisClient $predisClient)
    {
        $this->setPredisClient($predisClient);
        $this->predisClient->getProfile()->defineCommand('setnxex', Setnxex::class);
    }

    /**
     * @return self
     */
    public function set($key, $data)
    {
        // Prefix the key to act as a namespace so implementers don't accidentally overwrite our other values in Redis
        $this->predisClient->set(static::KEY_PREFIX . $key, $data);
        return $this;
    }

    /**
     * @return mixed
     */
    public function get($key)
    {
        return $this->predisClient->get(static::KEY_PREFIX . $key);
    }

    /**
     * @return self
     */
    public function remove($key)
    {
        $this->predisClient->del(static::KEY_PREFIX . $key);
        return $this;
    }

    protected function setPredisClient(PredisClient $predisClient)
    {
        $this->predisClient = $predisClient;
        return $this;
    }

    public function lockParcelNumber(Shipment $shipment, string $parcelNumberKey): void
    {
        $key = $this->getParcelNumberLockKey($parcelNumberKey);
        $result = $this->predisClient->setnxex($key, static::LOCK_EXPIRY_SECONDS, time());

        if ($result) {
            $this->logDebug('Locked parcelNumber for shipping account %s', [$shipment->getAccount()->getId()], static::LOG_CODE);
            return;
        }

        $count = 0;
        while (!$result) {

            if ($count >= static::LOCK_MAX_RETRIES) {
                $this->logDebug('Unable to lock parcelNumber for shipping account %s after %d tries. Throwing UserError', [$shipment->getAccount()->getId(), $count], static::LOG_CODE);
                throw new UserError('Unable to generate new parcel number, please try again.');
            }

            $count++;
            $this->logWarning('Unable to lock parcelNumber for shipping account %s, sleeping for %d microseconds, attempt %d of %d', [$shipment->getAccount()->getId(), static::LOCK_RETRY_WAIT_SECONDS, $count, static::LOCK_MAX_RETRIES], static::LOG_CODE);
            usleep(static::LOCK_RETRY_WAIT_MICROSECONDS);
            $result = $this->predisClient->setnxex($key, static::LOCK_EXPIRY_SECONDS, time());
        }

        $this->logDebug('Locked parcelNumber for shipping account %s after %d attempts', [$shipment->getAccount()->getId(), $count], static::LOG_CODE);
        return;
    }

    public function unlockParcelNumber(Shipment $shipment, string $parcelNumberKey): void
    {
        $this->remove($parcelNumberKey);
        $this->logDebug('Unlocked parcelNumber for shipping account %d', [$shipment->getAccount()->getId()], static::LOG_CODE);
    }

    protected function getParcelNumberLockKey(string $parcelNumberKey): string
    {
        return static::CACHE_PARCEL_NUMBER_LOCK_KEY_PREFIX . '-' . $parcelNumberKey;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }
}