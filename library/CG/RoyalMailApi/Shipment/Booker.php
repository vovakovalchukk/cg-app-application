<?php
namespace CG\RoyalMailApi\Shipment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\Provider\Implementation\Label;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\RequestInterface;
use CG\RoyalMailApi\Request\Shipment\Create as Request;
use CG\RoyalMailApi\Request\Shipment\Create\Domestic as DomesticRequest;
use CG\RoyalMailApi\Request\Shipment\Create\International as InternationalRequest;
use CG\RoyalMailApi\Request\Shipment\Label as LabelRequest;
use CG\RoyalMailApi\ResponseInterface;
use CG\RoyalMailApi\Response\Shipment\Completed\Item as ShipmentItem;
use CG\RoyalMailApi\Response\Shipment\Create as Response;
use CG\RoyalMailApi\Response\Shipment\Label as LabelResponse;
use CG\RoyalMailApi\Shipment;

class Booker
{
    const DOMESTIC_COUNTRY = 'GB';
    const ONE_D_BARCODE_PATTERN = '/[A-Z]{2}[0-9]{9}GB/';

    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function __invoke(Shipment $shipment): Shipment
    {
        $request = $this->buildRequestFromShipment($shipment);
        $response = $this->sendRequest($request, $shipment->getAccount());
        return $this->updateShipmentFromResponse($shipment, $response);
    }

    protected function buildRequestFromShipment(Shipment $shipment): Request
    {
        if ($this->isDomesticShipment($shipment)) {
            return new DomesticRequest($shipment);
        }
        return new InternationalRequest($shipment);
    }

    protected function isDomesticShipment(Shipment $shipment): bool
    {
        return ($shipment->getDeliveryAddress()->getISOAlpha2CountryCode() == static::DOMESTIC_COUNTRY);
    }

    protected function sendRequest(RequestInterface $request, CourierAdapterAccount $account): ResponseInterface
    {
        try {
            /** @var Client $client */
            $client = ($this->clientFactory)($account);
            return $client->send($request);
        } catch (\Exception $e) {
            throw new OperationFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function updateShipmentFromResponse(Shipment $shipment, Response $response): Shipment
    {
        $shipmentItems = $response->getShipmentItems();
        $shipmentNumbers = [];
        foreach ($shipmentItems as $shipmentItem) {
            $shipmentNumbers[] = $shipmentItem->getShipmentNumber();
        }
        $shipment->setCourierReference(implode('|', $shipmentNumbers));

        foreach ($shipment->getPackages() as $package) {
            $shipmentItem = current($shipmentItems);
            if (!$shipmentItem) {
                break;
            }
            $label = $shipmentItem->getLabel() ?? $this->fetchLabelForShipmentItem($shipmentItem, $shipment->getAccount());
            if ($label) {
                $package->setLabel(new Label($label, LabelInterface::TYPE_PDF));
            }
            $package->setTrackingReference($this->determineTrackingNumber($shipmentItem));
            next($shipmentItems);
        }
        return $shipment;
    }

    protected function fetchLabelForShipmentItem(ShipmentItem $shipmentItem, CourierAdapterAccount $account): ?string
    {
        $request = new LabelRequest($shipmentItem->getShipmentNumber());
        /** @var LabelResponse $response */
        $response = $this->sendRequest($request, $account);
        return $response->getLabel();
    }

    protected function determineTrackingNumber(ShipmentItem $shipmentItem): ?string
    {
        // The shipmentNumber is the 1D barcode when present, otherwise it's an RM internal ID
        if (preg_match(static::ONE_D_BARCODE_PATTERN, $shipmentItem->getShipmentNumber())) {
            return $shipmentItem->getShipmentNumber();
        }
        // Fallback to the 2D barcode number
        return $shipmentItem->getItemId();
    }
}