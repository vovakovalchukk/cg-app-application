<?php

namespace CG\ShipStation\Carrier\Label\Manifest;

use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Entity as AccountManifest;
use CG\ShipStation\Carrier\Label\Exception\InvalidResponse;
use CG\ShipStation\Client;
use CG\ShipStation\Request\Shipping\Manifest as ManifestRequest;
use CG\ShipStation\Response\Shipping\Manifest as ManifestResponse;
use CG\ShipStation\ShipStation\Service as ShipStationService;
use CG\Stdlib\DateTime;
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

    public function generateShipStationManifest(Account $shippingAccount, Account $shipStationAccount, AccountManifest $accountManifest): ?ManifestResponse
    {
        $warehouseId = $shipStationAccount->getExternalDataByKey('warehouseId');
        $dateTime = new DateTime();
        $manifestRequest = new ManifestRequest($shippingAccount->getExternalId(), $warehouseId, $dateTime);
        $this->client->sendRequest($manifestRequest, $shipStationAccount);
        $this->logDebug('Sending manifest creation request for account %s using warehouseID %s', [$shippingAccount->getId(), $warehouseId], static::LOG_CODE);
        try {
            /** @var ManifestResponse $response */
            $response = $this->client->sendRequest($manifestRequest, $shipStationAccount);
        } catch (\Exception $e) {
            $this->logCriticalException($e, 'Failed to create manifest for account %s using warehouseID %s', [$shippingAccount->getId(), $warehouseId], static::LOG_CODE);
            throw new ValidationException('Fail', $e->getCode(), $e);
        }

        if (empty($response->getFormId())) {
            $this->logCritical('Failed to retrieve field form_id from manifest creation response for account %s using warehouseID %s', [$shippingAccount->getId(), $warehouseId], static::LOG_CODE);
            return null;
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
}