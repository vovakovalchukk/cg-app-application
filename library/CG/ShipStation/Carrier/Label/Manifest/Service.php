<?php

namespace CG\ShipStation\Carrier\Label\Manifest;

use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Entity as AccountManifest;
use CG\ShipStation\Carrier\Label\Exception\InvalidResponse;
use CG\Account\Shared\Manifest\Filter as AccountManifestFilter;
use CG\ShipStation\Client;
use CG\ShipStation\Request\Shipping\Manifest as ManifestRequest;
use CG\ShipStation\Response\Shipping\Manifest as ManifestResponse;
use CG\ShipStation\ShipStation\Service as ShipStationService;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\ValidationException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Zendesk\Exception\Collection\Invalid;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\MultiTransferException;
use Psr\Log\LogLevel;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'ShipStationManifestService';

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

    /**
     * @param Account $shippingAccount
     * @param Account $shipStationAccount
     * @param AccountManifest $accountManifest
     * @return ManifestResponse[]
     */
    public function generateShipStationManifests(Account $shippingAccount, Account $shipStationAccount, AccountManifest $accountManifest): ?array
    {
        $this->logDebug('attempting to create manifest for shipstation account: %s belonging to OU: %s', [$shippingAccount->getId(), $shippingAccount->getOrganisationUnitId()], static::LOG_CODE);
        try {
            $lastManifestDate = $this->getLatestManifestDateForShippingAccount($shippingAccount);
            $this->logDebug('got last manifest date of %s for shipstation account: %s belonging to OU: %s', [$lastManifestDate->format('d-m-Y'), $shippingAccount->getOrganisationUnitId()], static::LOG_CODE);
        } catch (NotFound $exception) {
            $this->logDebug('failed to retrieve date of last manifest; falling back to account creation date for shipstation account: %s belonging to OU: %s', [$lastManifestDate->format('d-m-Y'), $shippingAccount->getOrganisationUnitId()], static::LOG_CODE);
            $lastManifestDate = $shippingAccount->getCgCreationDate();
        }

        $today = new DateTime("today");
        $datesToManifest = $this->determineDatesRequiringManifests($lastManifestDate, $today);
        $totalNumberOfManifests = count($datesToManifest);
        $this->logDebug('%u manifests required for OU: %s dating from %s to %s'. [$totalNumberOfManifests, $shippingAccount->getOrganisationUnitId(), $lastManifestDate->format('d-m-y'), $today->format('d-m-y')], static::LOG_CODE);

        $currentManifest = 0;
        $responses = [];
        /** @var \DateTime $manifestDate */
        foreach ($datesToManifest as $manifestDate) {
            $currentManifest++;
            $this->logDebug('Attempting to create manifest %u of %u for OU: %s, dated %s', [$currentManifest, $totalNumberOfManifests, $shippingAccount->getOrganisationUnitId(), $manifestDate->format('d-m-y')]);
            $warehouseId = $shipStationAccount->getExternalDataByKey('warehouseId');
            $manifestRequest = new ManifestRequest($shippingAccount->getExternalId(), $warehouseId, $manifestDate);
            $this->logDebug('Sending manifest creation request for account %s using warehouseID %s', [$shippingAccount->getId(), $warehouseId], static::LOG_CODE);
            try {
                /** @var ManifestResponse $response */
                $response = $this->client->sendRequest($manifestRequest, $shipStationAccount);
                $responses[] = $response;
            } catch (\Exception $e) {
                $this->logCriticalException($e, 'Failed to create manifest for account %s using warehouseID %s', [$shippingAccount->getId(), $warehouseId], static::LOG_CODE);
                throw new ValidationException('Fail', $e->getCode(), $e);
            }

            if (empty($response->getFormId())) {
                $this->logCritical('Failed to retrieve field form_id from manifest creation response for account %s using warehouseID %s', [$shippingAccount->getId(), $warehouseId], static::LOG_CODE);
                return null;
            }
        }

        return $response;
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
            $this->logCriticalException($e, 'Failed to download PDF of manifest %s at URL %S', [$manifest->getFormId(), $manifest->getManifestDownload()->getHref()], static::LOG_CODE);
        }
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

    protected function determineDatesRequiringManifests(DateTime $lastManifestDate, DateTime $today)
    {
        return new \DatePeriod($lastManifestDate, new \DateInterval('P1D') ,$today);
    }
}