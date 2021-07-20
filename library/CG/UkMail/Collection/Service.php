<?php
namespace CG\UkMail\Collection;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\UserError;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Client\Factory as ClientFactory;
use CG\UkMail\Request\Rest\Collection as CollectionRequest;
use CG\UkMail\Response\Rest\Collection as CollectionResponse;
use Predis\Client as PredisClient;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'UkMailCollectionService';
    protected const LOG_FETCHING_COLLECTION_JOB_MSG = 'Fetching UK Mail collection job number %s for account %d from redis';
    protected const LOG_FETCHING_COLLECTION_JOB_API_MSG = 'Fetching UK Mail collection job number for account %d from UK Mail API';
    protected const LOG_SAVING_COLLECTION_JOB_MSG = 'Saving UK Mail collection job number %s for account %d to redis';

    protected const DEFAULT_EARLIEST_TIME = '09:00';
    protected const DEFAULT_LATEST_TIME = '17:00';

    protected const COLLECTION_TYPE_DOMESTIC = 'D';
    protected const COLLECTION_TYPE_INTERNATIONAL = 'I';

    protected const COLLECTION_JOB_KEY = 'CGUkMailCollectionJobNumber:%d:%s:%s';
    protected const COLLECTION_JOB_TTL = 604800; //7 days

    /** @var PredisClient */
    protected $predisClient;
    /** @var ClientFactory */
    protected $clientFactory;

    protected $collectionJobNumbers = [];

    public function __construct(PredisClient $predisClient, ClientFactory $clientFactory)
    {
        $this->predisClient = $predisClient;
        $this->clientFactory = $clientFactory;
    }

    public function getCollectionJobNumber(
        CourierAdapterAccount $account,
        string $authToken,
        \DateTime $collectionDate,
        bool $isDomestic = true
    ): string {
        $type = $this->getType($isDomestic);
        $date = $collectionDate->format('Ymd');
        if (isset($this->collectionJobNumbers[$account->getId()][$date][$type])) {
            return $this->collectionJobNumbers[$account->getId()][$date][$type];
        }

        if (($collectionJobNumber = $this->fetchCollectionJobNumber($account, $collectionDate, $isDomestic)) != null) {
            $this->logDebug(static::LOG_FETCHING_COLLECTION_JOB_MSG, [$collectionJobNumber, $account->getId()], static::LOG_CODE);
            $this->collectionJobNumbers[$account->getId()][$date][$type] = $collectionJobNumber;
            return $collectionJobNumber;
        }

        $collectionResponse = $this->requestCollection($account, $authToken, $collectionDate, $isDomestic);

        $this->saveCollectionJobNumber($account, $collectionDate, $collectionResponse->getCollectionJobNumber(), $isDomestic);
        $this->collectionJobNumbers[$account->getId()][$date][$type] = $collectionResponse->getCollectionJobNumber();
        return $collectionResponse->getCollectionJobNumber();
    }

    protected function requestCollection(
        CourierAdapterAccount $account,
        string $authToken,
        \DateTime $collectionDate,
        bool $isDomestic
    ): CollectionResponse {
        $this->logDebug(static::LOG_FETCHING_COLLECTION_JOB_API_MSG, [$account->getId()], static::LOG_CODE);
        $collectionRequest = $this->createCollectionRequest($account, $authToken, $collectionDate, $isDomestic);
        try {
            $client = ($this->clientFactory)($account, $collectionRequest);
            return $client->sendRequest($collectionRequest);
        }  catch (\Exception $exception) {
            throw new UserError($exception->getMessage());
        }
    }

    protected function handleError(\Exception $exception)
    {

    }

    protected function createCollectionRequest(CourierAdapterAccount $account, string $authToken, \DateTime $collectionDate, bool $isDomestic): CollectionRequest
    {
        $accountNumber = $account->getCredentials()['domesticAccountNumber'];
        if (!$isDomestic) {
            $accountNumber = $account->getCredentials()['intlAccountNumber'];
        }

        return new CollectionRequest(
            $account->getCredentials()['apiKey'],
            $account->getCredentials()['username'],
            $authToken,
            $accountNumber,
            $collectionDate->format('Y-m-d'),
            $account->getConfig()['closedForLunch'] ?? false,
            $account->getConfig()['earliestTime'] ?? static::DEFAULT_EARLIEST_TIME,
            $account->getConfig()['latestTime'] ?? static::DEFAULT_LATEST_TIME,
            $account->getConfig()['specialInstructions'] ?? ''
        );
    }

    protected function fetchCollectionJobNumber(CourierAdapterAccount $account, \DateTime $collectionDate, bool $isDomestic): ?string
    {
        return $this->predisClient->get($this->getKey($account, $collectionDate, $isDomestic));
    }

    protected function saveCollectionJobNumber(
        CourierAdapterAccount $account,
        \DateTime $collectionDate,
        string $collectionJobNumber,
        bool $isDomestic
    ): void {
        $this->logDebug(static::LOG_SAVING_COLLECTION_JOB_MSG, [$collectionJobNumber, $account->getId()], static::LOG_CODE);
        $this->predisClient->setex(
            $this->getKey($account, $collectionDate, $isDomestic),
            static::COLLECTION_JOB_TTL,
            $collectionJobNumber
        );
    }

    protected function getKey(CourierAdapterAccount $account, \DateTime $collectionDate, bool $isDomestic): string
    {
        $type = $this->getType($isDomestic);
        return sprintf(static::COLLECTION_JOB_KEY, $account->getId(), $type, $collectionDate->format('Ymd'));
    }

    protected function getType(bool $isDomestic): string
    {
        $type = static::COLLECTION_TYPE_DOMESTIC;
        if (!$isDomestic) {
            $type = static::COLLECTION_TYPE_INTERNATIONAL;
        }
        return $type;
    }
}