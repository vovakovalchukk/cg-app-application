<?php
namespace CG\ShipStation\Request\Shipping;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Collection as OrderCollection;
use CG\ShipStation\Messages\Shipment;
use CG\ShipStation\Messages\ShipmentAddress;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\Shipments as Response;

class Shipments extends RequestAbstract
{
    const METHOD = 'POST';
    const URI = '/shipments';

    /** @var Shipment[] */
    protected $shipments;

    public function __construct(Shipment ...$shipments)
    {
        $this->shipments = $shipments;
    }

    public function toArray(): array
    {
        return ['shipments' => $this->getShipmentsArray()];
    }

    protected function getShipmentsArray(): array
    {
        $shipments = [];
        foreach ($this->shipments as $shipment) {
            $shipments[] = $shipment->toArray();
        }
        return $shipments;
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public static function createFromOrdersAndData(
        OrderCollection $orders,
        array $ordersData,
        array $orderParcelsData,
        Account $shipStationAccount,
        Account $shippingAccount
    ): Shipments {
        $shipments = [];
        foreach ($orders as $order) {
            $orderData = $ordersData[$order->getId()];
            $parcelsData = $orderParcelsData[$order->getId()];
            $shipments[] = Shipment::createFromOrderAndData($order, $orderData, $parcelsData, $shipStationAccount, $shippingAccount);
        }

        return new static(...$shipments);
    }
}