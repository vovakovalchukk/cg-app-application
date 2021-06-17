<?php
namespace CG\Hermes\Shipment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Exception\UserError;
use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\Provider\Implementation\Label;
use CG\Hermes\Client;
use CG\Hermes\Client\Factory as ClientFactory;
use CG\Hermes\Request\RouteDeliveryCreatePreadviceAndLabel as Request;
use CG\Hermes\Response\RouteDeliveryCreatePreadviceAndLabel as Response;
use CG\Hermes\Shipment;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function bookShipment(Shipment $shipment): Shipment
    {
        $request = $this->buildRequestFromShipment($shipment);

        $this->logDebugDump($shipment, 'SHIPMENT', [], 'MYTEST');
        $this->logDebugDump($request, 'REQUEST', [], 'MYTEST');
        $this->logDebugDump($shipment->getAccount()->getCredentials(), 'CRED', [], 'MYTEST');



        $response = $this->sendRequest($request, $shipment->getAccount());
        return $this->updateShipmentFromResponse($shipment, $response);
    }

    protected function buildRequestFromShipment(Shipment $shipment): Request
    {
        return new Request($shipment, $shipment->getDeliveryService());
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
        $courierReference = $response->getBarcodeNumbers() ? $response->getBarcodeNumbers()[0] : '';
        $shipment->setCourierReference($courierReference);
        $labels = $response->getLabels();
        $barcodes = $response->getBarcodeNumbers();
        foreach ($shipment->getPackages() as $package) {
            $labelData = current($labels);
            if ($labelData) {
                $package->setLabel(new Label($labelData, LabelInterface::TYPE_PDF));
            }
            $barcode = current($barcodes) ?? '';
            $package->setTrackingReference($barcode);
            next($labels);
            next($barcodes);
        }
        return $shipment;
    }
}