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
    const SHIPPING_ACCOUNT_REQUEST_STORAGE_KEY_TEMPLATE = '%s-%s-accountConnectionData';

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

        if ($this->acquireLock($key) === 1) {
            $this->logSuccessfulLock($shipment, 1);
            return;
        }

        $count = 0;
        while ($this->acquireLockWithSleep($key) === 0) {
            $this->isTriesExceeded($shipment, $count);
            $this->incrementCount($shipment, $count);
        }

        $this->logSuccessfulLock($shipment, $count);
        return;
    }

    public function unlockParcelNumber(Shipment $shipment, string $parcelNumberKey): void
    {
        $this->remove($this->getParcelNumberLockKey($parcelNumberKey));
        $this->logDebug('Unlocked parcelNumber for shipping account %d', [$shipment->getAccount()->getId()], [static::LOG_CODE, 'lockReleased']);
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

    protected function isTriesExceeded(Shipment $shipment, int $count)
    {
        if ($count >= static::LOCK_MAX_RETRIES) {
            $this->logDebug('Unable to lock parcelNumber for shipping account %s after %d tries. Throwing UserError', [$shipment->getAccount()->getId(), $count], [static::LOG_CODE, 'lockFailed']);
            throw new UserError('Unable to generate new parcel number, please try again.');
        }
    }

    protected function acquireLock(string $key): int
    {
        return $this->predisClient->setnxex(static::KEY_PREFIX . $key, static::LOCK_EXPIRY_SECONDS, time());
    }

    protected function acquireLockWithSleep(string $key, int $microseconds = null)
    {
        usleep($microseconds ?? static::LOCK_RETRY_WAIT_MICROSECONDS);
        return $this->acquireLock($key);
    }

    protected function logSuccessfulLock(Shipment $shipment, int $count): void
    {
        $this->logDebug('Locked parcelNumber for shipping account %s after %d attempt(s)', [$shipment->getAccount()->getId(), $count], [static::LOG_CODE, 'LockAcquired'], ['parcelNumberLockAttempts' => $count]);
    }

    protected function incrementCount(Shipment $shipment, int &$count)
    {
        $count++;
        $this->logWarning('Unable to lock parcelNumber for shipping account %s, sleeping for %d microseconds, attempt %d of %d', [$shipment->getAccount()->getId(), static::LOCK_RETRY_WAIT_MICROSECONDS, $count, static::LOCK_MAX_RETRIES], [static::LOG_CODE, 'lockFailed']);
    }
}