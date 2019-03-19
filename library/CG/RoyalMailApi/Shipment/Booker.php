<?php
namespace CG\RoyalMailApi\Shipment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Exception\UserError;
use CG\CourierAdapter\LabelInterface;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Request\Shipment\Create as Request;
use CG\RoyalMailApi\Response\Shipment\Create as Response;
use CG\RoyalMailApi\Shipment;
use CG\RoyalMailApi\Shipment\Label;

class Booker
{
    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
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
        return new Request($shipment);
    }

    protected function sendRequest(Request $request, CourierAdapterAccount $account)
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
        //TODO!
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