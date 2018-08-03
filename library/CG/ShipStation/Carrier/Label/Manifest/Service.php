<?php

namespace CG\ShipStation\Carrier\Label\Manifest;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Entity as AccountManifest;
use CG\ShipStation\Client;
use CG\ShipStation\Request\Shipping\Manifest as ManifestRequest;
use CG\ShipStation\Response\Shipping\Manifest as ManifestResponse;
use CG\ShipStation\ShipStation\Service as ShipStationService;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\ValidationException;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\MultiTransferException;

class Service
{
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
        //string $carrierId, $warehouseId, $shipDate, $excludedLabelIds = []
        $warehouseId = $shipStationAccount->getExternalDataByKey('warehouseId');
        $dateTime = new DateTime($shippingAccount->getCgCreationDate());
        $manifestRequest = new ManifestRequest($shippingAccount->getExternalId(), $warehouseId, $dateTime);
        $this->client->sendRequest($manifestRequest, $shipStationAccount);
        try {
            /** @var ManifestResponse $response */
            $response = $this->client->sendRequest($manifestRequest, $shipStationAccount);
        } catch (\Exception $e) {
            throw new ValidationException('Fail', $e->getCode(), $e);
        }

        if (empty($response->getFormId())) {
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

        }
    }
}