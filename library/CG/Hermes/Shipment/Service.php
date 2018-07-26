<?php
namespace CG\Hermes\Shipment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Exception\UserError;
use CG\CourierAdapter\LabelInterface;
use CG\Hermes\Client;
use CG\Hermes\Client\Factory as ClientFactory;
use CG\Hermes\DeliveryService\Service as DeliveryServiceService;
use CG\Hermes\Request\RouteDeliveryCreatePreadviceAndLabel as Request;
use CG\Hermes\Response\RouteDeliveryCreatePreadviceAndLabel as Response;
use CG\Hermes\Shipment;
use CG\Hermes\Shipment\Label;

class Service
{
    /** @var DeliveryServiceService */
    protected $deliveryServiceService;
    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(DeliveryServiceService $deliveryServiceService, ClientFactory $clientFactory)
    {
        $this->deliveryServiceService = $deliveryServiceService;
        $this->clientFactory = $clientFactory;
    }

    public function bookShipment(Shipment $shipment): Shipment
    {
        $request = $this->buildRequestFromShipment($shipment);
        $response = $this->sendRequest($request, $shipment->getAccount());
        return $this->updateShipmentFromResponse($shipment, $response);
    }

    protected function buildRequestFromShipment(Shipment $shipment): Request
    {
        $deliveryService = $this->deliveryServiceService->getDeliveryServiceByReference($shipment->getDeliveryService());
        return new Request($shipment, $deliveryService);
    }

    protected function sendRequest(Request $request, CourierAdapterAccount $account)
    {
        try {
            /** @var Client $client */
            $client = ($this->clientFactory)($account);
            return $client->sendRequest($request);
        } catch (\Exception $e) {
            throw new OperationFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function updateShipmentFromResponse(Shipment $shipment, Response $response): Shipment
    {
        if (!empty($response->getErrorMessages())) {
            throw new UserError(implode('; ', $response->getErrorMessages()));
        }
        // There is no courier reference as such, just use the first barcode
        $courierReference = $response->getBarcodeNumbers() ? array_shift($response->getBarcodeNumbers()) : '';
        $shipment->setCourierReference($courierReference);
        $shipment->setTrackingReferences($response->getBarcodeNumbers());
        foreach ($response->getLabels() as $labelData) {
            $shipment->addLabel(new Label($labelData, LabelInterface::TYPE_PDF));
        }
        return $shipment;
    }
}