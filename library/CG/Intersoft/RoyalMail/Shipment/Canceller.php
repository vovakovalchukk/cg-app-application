<?php
namespace CG\Intersoft\RoyalMail\Shipment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\Intersoft\Client;
use CG\Intersoft\Client\Factory as ClientFactory;
use CG\Intersoft\RoyalMail\Request\Shipment\Cancel as CancelRequest;
use CG\Intersoft\RoyalMail\Response\Shipment\Cancel as CancelResponse;
use CG\Intersoft\RoyalMail\Shipment;

class Canceller
{
    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function __invoke(Shipment $shipment): void
    {
        $shipmentNumbers = $this->getShipmentNumbersFromShipment($shipment);
        foreach ($shipmentNumbers as $shipmentNumber) {
            $request = new CancelRequest($shipmentNumber);
            $this->sendRequest($request, $shipment->getAccount());
        }
    }

    protected function getShipmentNumbersFromShipment(Shipment $shipment): array
    {
        return explode(Booker::SHIP_NO_SEP, $shipment->getCourierReference());
    }

    protected function sendRequest(CancelRequest $request, CourierAdapterAccount $account): CancelResponse
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