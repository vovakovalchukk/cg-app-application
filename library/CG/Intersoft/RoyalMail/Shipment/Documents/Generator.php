<?php
namespace CG\Intersoft\RoyalMail\Shipment\Documents;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\ExchangeRate\Service as ExchangeRateService;
use CG\Intersoft\Client;
use CG\Intersoft\Client\Factory as ClientFactory;
use CG\Intersoft\RoyalMail\Request\Shipment\Documents as DocumentsRequest;
use CG\Intersoft\RoyalMail\Response\Shipment\Completed\Item as ShipmentItem;
use CG\Intersoft\RoyalMail\Response\Shipment\Documents as DocumentsResponse;
use CG\Intersoft\RoyalMail\Shipment;
use CG\Intersoft\RoyalMail\Shipment\Package;

class Generator
{
    const CN22_MAX_VALUE_GBP = 270;

    /** @var ClientFactory */
    protected $clientFactory;
    /** @var ExchangeRateService */
    protected $exchangeRateService;

    public function __construct(ClientFactory $clientFactory, ExchangeRateService $exchangeRateService)
    {
        $this->clientFactory = $clientFactory;
        $this->exchangeRateService = $exchangeRateService;
    }

    public function __invoke(string $trackingNumber, Shipment $shipment): ?string
    {
        $request = new DocumentsRequest($trackingNumber);
        /** @var DocumentsResponse $response */
        $response = $this->sendRequest($request, $shipment->getAccount());
        return $response->getDocumentImage();
    }

    protected function sendRequest(DocumentsRequest $request, CourierAdapterAccount $account): DocumentsResponse
    {
        try {
            /** @var Client $client */
            $client = ($this->clientFactory)($account);
            return $client->send($request);
        } catch (\Exception $e) {
            throw new OperationFailed($e->getMessage(), $e->getCode(), $e);
        }
    }
}