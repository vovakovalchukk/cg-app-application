<?php
namespace CG\RoyalMailApi\Shipment\Label;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Request\Shipment\Label as Request;
use CG\RoyalMailApi\Response\Shipment\Completed\Item as ShipmentItem;
use CG\RoyalMailApi\Response\Shipment\Label as Response;
use CG\RoyalMailApi\Shipment;

class Generator
{
    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function __invoke(ShipmentItem $shipmentItem, Shipment $shipment): ?string
    {
        $request = new Request($shipmentItem->getShipmentNumber());
        /** @var Response $response */
        $response = $this->sendRequest($request, $shipment->getAccount());
        return $response->getLabel();
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