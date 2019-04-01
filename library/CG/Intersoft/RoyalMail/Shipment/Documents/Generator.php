<?php
namespace CG\Intersoft\RoyalMail\Shipment\Documents;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\ExchangeRate\Service as ExchangeRateService;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Request\Shipment\Documents as DocumentsRequest;
use CG\RoyalMailApi\Response\Shipment\Completed\Item as ShipmentItem;
use CG\RoyalMailApi\Response\Shipment\Documents as DocumentsResponse;
use CG\RoyalMailApi\Shipment;
use CG\RoyalMailApi\Shipment\Package;

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

    public function __invoke(ShipmentItem $shipmentItem, Shipment $shipment): ?string
    {
        $requiredDocument = $this->determineRequiredDocument($shipmentItem, $shipment);
        $request = new DocumentsRequest($shipmentItem->getShipmentNumber(), $requiredDocument);
        /** @var DocumentsResponse $response */
        $response = $this->sendRequest($request, $shipment->getAccount());
        return $response->getInternationalDocument();
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

    protected function determineRequiredDocument(ShipmentItem $shipmentItem, Shipment $shipment): string
    {
        $package = $this->getPackageForShipmentItem($shipmentItem, $shipment);
        $totalValue = $this->getTotalValueOfPackageInGBP($package);
        if ($totalValue <= static::CN22_MAX_VALUE_GBP) {
            return DocumentsRequest::DOCUMENT_CN22;
        }
        return DocumentsRequest::DOCUMENT_CN23;
    }

    protected function getPackageForShipmentItem(ShipmentItem $shipmentItem, Shipment $shipment): Package
    {
        /** @var Package $package */
        foreach ($shipment->getPackages() as $package) {
            if ($package->getRmShipmentNumber() == $shipmentItem->getShipmentNumber()) {
                return $package;
            }
        }
        throw new \RuntimeException('Could not find Package for Shipment Item ' . $shipmentItem->getShipmentNumber());
    }

    protected function getTotalValueOfPackageInGBP(Package $package): float
    {
        $totalValue = 0;
        foreach ($package->getContents() as $content) {
            $contentValue = $content->getUnitValue() * $content->getQuantity();
            if ($content->getUnitCurrency() != 'GBP') {
                $contentValue = $this->exchangeRateService->convertAmount($content->getUnitCurrency(), 'GBP', $contentValue);
            }
            $totalValue += $contentValue;
        }
        return $totalValue;
    }
}