<?php

namespace CG\ShipStation\Carrier\Label\Manifest;

use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Entity as AccountManifest;
use CG\ShipStation\Carrier\Label\Exception\InvalidResponse;
use CG\Account\Shared\Manifest\Filter as AccountManifestFilter;
use CG\ShipStation\Carrier\Label\Manifest\Exception\IncompleteManifestException;
use CG\ShipStation\Client;
use CG\ShipStation\Exception\GatewayTimeout;
use CG\ShipStation\Request\Shipping\Manifest\Create as ManifestRequest;
use CG\ShipStation\Request\Shipping\Manifest\Query as ManifestQuery;
use CG\ShipStation\Response\Shipping\Manifest\Create as ManifestResponse;
use CG\ShipStation\ShipStation\Service as ShipStationService;
use CG\Stdlib\Date;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\ValidationException;
use CG\Stdlib\Exception\Storage;
use function CG\Stdlib\mergePdfData;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Zendesk\Exception\Collection\Invalid;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\MultiTransferException;
use Psr\Log\LogLevel;
use CG\Stdlib\Exception\Storage as StorageException;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'ShipStationManifestService';
    protected const GATEWAY_TIMEOUT_WAIT = 1;

    /** @var ShipStationService */
    protected $shipStationService;
    /** @var Client */
    protected $client;
    /** @var GuzzleClient */
    protected $guzzleClient;

    public function __construct(
        ShipStationService $shipStationService,
        Client $client,
        GuzzleClient $guzzleClient
    ) {
        $this->shipStationService = $shipStationService;
        $this->client = $client;
        $this->guzzleClient = $guzzleClient;
    }

    public function generateShipStationManifest(
        Account $shippingAccount,
        Account $shipStationAccount,
        AccountManifest $accountManifest,
        DateTime $lastManifestDate
    ): void {
        $this->logDebug('attempting to create manifest(s) for shipstation account %s belonging to OU %s', [$shippingAccount->getId(), $shippingAccount->getOrganisationUnitId()], [static::LOG_CODE, 'CreateManifest']);

        // We use tomorrow to include any manifests that need generating for today
        $tomorrow = new DateTime("tomorrow");
        $datesToManifest = $this->determineDatesRequiringManifests($lastManifestDate, $tomorrow);
        $totalNumberOfManifests = count($datesToManifest);
        $this->logDebug('%u manifest(s) required for OU %s dating from %s to %s', [$totalNumberOfManifests, $shippingAccount->getOrganisationUnitId(), $lastManifestDate->format('d-m-y'), $tomorrow->format('d-m-y')], [static::LOG_CODE, 'CreateManifest']);
        $this->requestManifestsFromShipStation($shippingAccount, $shipStationAccount, $datesToManifest, $totalNumberOfManifests, $accountManifest);
    }

    public function retrievePdfForManifest(ManifestResponse $manifest)
    {
        $request = $this->guzzleClient->get($manifest->getManifestDownload()->getHref());
        try {
            $request->send();
            $response = $request->getResponse();
            $pdf = $response->getBody(true);
            return $pdf;
        } catch (MultiTransferException $e) {
            $this->logCriticalException($e, 'Failed to download PDF of manifest %s at URL %S', [$manifest->getFormId(), $manifest->getManifestDownload()->getHref()], [static::LOG_CODE, 'CreateManifest']);
        }
    }

    protected function requestManifestsFromShipStation(
        Account $shippingAccount,
        Account $shipStationAccount,
        \DatePeriod $datesToManifest,
        int $totalNumberOfManifests,
        AccountManifest $accountManifest
    ): void {
        $currentManifest = 0;
        $responses = [];
        $failedRequests = [];
        $beginCreationTime = strtotime('now');
        /** @var \DateTime $manifestDate */
        foreach ($datesToManifest as $manifestDate) {
            $currentManifest++;
            $this->logDebug('Attempting to create manifest %u of %u for OU %s, dated %s', [$currentManifest, $totalNumberOfManifests, $shippingAccount->getOrganisationUnitId(), $manifestDate->format('d-m-y')], [static::LOG_CODE, 'CreateManifest']);
            $warehouseId = $shipStationAccount->getExternalDataByKey('warehouseId');
            $manifestRequest = new ManifestRequest($shippingAccount->getExternalId(), $warehouseId, $manifestDate);
            $this->logDebug('Sending manifest creation request for account %s using warehouseID %s', [$shippingAccount->getId(), $warehouseId], [static::LOG_CODE, 'CreateManifest']);
            try {
                /** @var ManifestResponse $response */
                $response = $this->client->sendRequest($manifestRequest, $shipStationAccount);
                $responses[] = $response;
            } catch (StorageException $e) {
                if (count($responses) == 0) {
                    throw $e;
                }
                $this->endManifestingEarly($shippingAccount, $e, $responses, $warehouseId, $currentManifest, $totalNumberOfManifests, $accountManifest);
                throw new IncompleteManifestException('Failed to complete manifest', $e->getCode(), $e);
            } catch (GatewayTimeout $e) {
                $this->logNotice('Received timeout response from shipstation for manifest %u of %u, dated %s. Will attempt to retrieve this at the end', [$currentManifest, $totalNumberOfManifests, $manifestDate->format('d-m-y')], [static::LOG_CODE, 'CreateManifest']);
                $failedRequests['timeout'][] = $manifestRequest;
            }
        }

        if (isset($failedRequests['timeout']) && count($failedRequests['timeout']) > 0) {
            $responses = $this->handleTimeoutResponse($beginCreationTime, $shipStationAccount) ?? $responses;
        }
        $this->mergeManifests($responses, $accountManifest);
    }

    protected function endManifestingEarly(
        Account $shippingAccount,
        \Exception $exception,
        array $responses,
        $warehouseId,
        int $currentManifest,
        int $totalNumberOfManifests,
        AccountManifest $accountManifest
    )
    {
        $this->logCriticalException($exception, 'Failed to create manifest %u of %u for account %s for OU %s', [$currentManifest, $totalNumberOfManifests, $shippingAccount->getId(), $warehouseId, $shippingAccount->getOrganisationUnitId()], [static::LOG_CODE, 'CreateManifest']);
        $this->logInfo('Stopping manifest creation early for account %s at manifest %u of %u for OU %s', [$shippingAccount->getId(), $currentManifest, $totalNumberOfManifests, $shippingAccount->getOrganisationUnitId()], [static::LOG_CODE, 'CreateManifest']);
        $this->mergeManifests($responses, $accountManifest);
    }

    protected function mergeManifests(array $responses, AccountManifest $accountManifest)
    {
        $finalFormId = '';
        $pdfs = [];
        /** @var ManifestResponse $response */
        foreach ($responses as $response) {
            $finalFormId .= $response->getFormId();
            $pdfs[] = $this->retrievePdfForManifest($response);
        }
        $mergedPdfs = $this->mergeManifestPdfs($pdfs);
        $accountManifest->setExternalId($finalFormId);
        $accountManifest->setManifest(base64_encode($mergedPdfs));
    }

    protected function mergeManifestPdfs(array $manifestPdfs): string
    {
        return mergePdfData($manifestPdfs);
    }

    /**
     * @return DateTime|null
     */
    protected function getLatestManifestDateForShippingAccount(Account $account)
    {
        $filter = (new AccountManifestFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setAccountId([$account->getId()])
            ->setOrderBy('created')
            ->setOrderDirection('DESC');
        $manifests = $this->accountManifestService->fetchCollectionByFilter($filter);
        $manifests->rewind();
        return new DateTime($manifests->current()->getCreated());
    }

    protected function determineDatesRequiringManifests(DateTime $lastManifestDate, DateTime $tomorrow)
    {
        $interval = new \DateInterval('P1D');
        return new \DatePeriod($lastManifestDate, $interval, $tomorrow);
    }

    protected function handleTimeoutResponse(string $beginCreationTimestamp, Account $shipStationAccount)
    {
        sleep(static::GATEWAY_TIMEOUT_WAIT);
        $creationFromTime = new DateTime("@$beginCreationTimestamp");
        return $this->fetchShipsStationManifestsSinceDate($creationFromTime, $shipStationAccount);
    }

    protected function fetchShipsStationManifestsSinceDate(DateTime $earliestDate, Account $shipStationAccount)
    {
        $manifestQuery = new ManifestQuery(
            $shipStationAccount->getExternalDataByKey('warehouseId'),
            null,
            null,
            null,
            $earliestDate
        );
        try {
            $responses = [];
            $manifestResponses = $this->client->sendRequest($manifestQuery, $shipStationAccount);
            /** @var ManifestResponse $manifest */
            foreach ($manifestResponses->getManifests() as $manifest) {
                $creationDate = new DateTime($manifest->getCreatedAt());
                $manifestCreationTime = $creationDate->getTimestamp() - $earliestDate->getTimestamp();
                $this->logDebug('Manifest received with creation time of %s, request started at %s, manifest creation took %s seconds', ['manifestCreationDate' => $manifest->getCreatedAt(), 'requestStarted' => $earliestDate->format(DateTime::ISO8601), 'manifestCreationDuration' => $manifestCreationTime], [static::LOG_CODE, 'CreateManifest']);
                $responses[] = $manifest;
            }
            return $responses;
        } catch (StorageException $e) {
            $this->logCriticalException($e, 'Some manifests have been created but we have been unable to retrieve them from shipstation.');
        }
    }
}