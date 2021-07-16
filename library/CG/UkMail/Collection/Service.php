<?php
namespace CG\UkMail\Collection;

use CG\CourierAdapter\Account as CourierAdapterAccount;
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

    protected const COLLECTION_JOB_KEY = 'CGUkMailCollectionJobNumber:%d:%s';
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

    public function getCollectionJobNumber(CourierAdapterAccount $account, string $authToken, \DateTime $collectionDate): string
    {
        $date = $collectionDate->format('Ymd');
        if (isset($this->collectionJobNumbers[$account->getId()][$date])) {
            return $this->collectionJobNumbers[$account->getId()][$date];
        }

        if (($collectionJobNumber = $this->fetchCollectionJobNumber($account, $collectionDate)) != null) {
            $this->logDebug(static::LOG_FETCHING_COLLECTION_JOB_MSG, [$collectionJobNumber, $account->getId()], static::LOG_CODE);
            $this->collectionJobNumbers[$account->getId()][$date] = $collectionJobNumber;
            return $collectionJobNumber;
        }

        $collectionResponse = $this->requestCollection($account, $authToken, $collectionDate);

        $this->saveCollectionJobNumber($account, $collectionDate, $collectionResponse->getCollectionJobNumber());
        $this->collectionJobNumbers[$account->getId()][$date] = $collectionResponse->getCollectionJobNumber();
        return $collectionResponse->getCollectionJobNumber();
    }

    protected function requestCollection(CourierAdapterAccount $account, string $authToken, \DateTime $collectionDate): CollectionResponse
    {
        $this->logDebug(static::LOG_FETCHING_COLLECTION_JOB_API_MSG, [$account->getId()], static::LOG_CODE);
        $collectionRequest = $this->createCollectionRequest($account, $authToken, $collectionDate);
        $client = ($this->clientFactory)($account, $collectionRequest);
        return $client->sendRequest($collectionRequest);
    }

    protected function createCollectionRequest(CourierAdapterAccount $account, string $authToken, \DateTime $collectionDate): CollectionRequest
    {
        return new CollectionRequest(
            $account->getCredentials()['apiKey'],
            $account->getCredentials()['username'],
            $authToken,
            $account->getCredentials()['accountNumber'],
            $collectionDate->format('Y-m-d'),
            $account->getConfig()['closedForLunch'] ?? false,
            $account->getConfig()['earliestTime'] ?? static::DEFAULT_EARLIEST_TIME,
            $account->getConfig()['latestTime'] ?? static::DEFAULT_LATEST_TIME,
            $account->getConfig()['specialInstructions'] ?? ''
        );
    }

    protected function fetchCollectionJobNumber(CourierAdapterAccount $account, \DateTime $collectionDate): ?string
    {
        return $this->predisClient->get($this->getKey($account, $collectionDate));
    }

    protected function saveCollectionJobNumber(CourierAdapterAccount $account, \DateTime $collectionDate, string $collectionJobNumber): void
    {
        $this->logDebug(static::LOG_SAVING_COLLECTION_JOB_MSG, [$collectionJobNumber, $account->getId()], static::LOG_CODE);
        $this->predisClient->setex(
            $this->getKey($account, $collectionDate),
            static::COLLECTION_JOB_TTL,
            $collectionJobNumber
        );
    }

    protected function getKey(CourierAdapterAccount $account, \DateTime $collectionDate): string
    {
        return sprintf(static::COLLECTION_JOB_KEY, $account->getId(), $collectionDate->format('Ymd'));
    }
}