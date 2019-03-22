<?php
namespace CG\RoyalMailApi\Shipment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Request\Shipment\Cancel as Request;
use CG\RoyalMailApi\Response\Shipment\Cancel as Response;
use CG\RoyalMailApi\Shipment;

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
            $request = new Request($shipmentNumber);
            $this->sendRequest($request, $shipment->getAccount());
        }
    }

    protected function getShipmentNumbersFromShipment(Shipment $shipment): array
    {
        return explode(Booker::SHIP_NO_SEP, $shipment->getCourierReference());
    }

    protected function sendRequest(Request $request, CourierAdapterAccount $account): Response
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