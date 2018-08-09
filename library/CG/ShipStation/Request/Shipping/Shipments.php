<?php
namespace CG\ShipStation\Request\Shipping;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\ShipStation\Messages\Shipment;
use CG\ShipStation\Messages\ShipmentAddress;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\Shipments as Response;
use CG\OrganisationUnit\Entity as OrganisationUnit;

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
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $orderParcelsData,
        Account $shipStationAccount,
        Account $shippingAccount,
        OrganisationUnit $rootOu
    ): Shipments {
        $shipments = [];
        foreach ($orders as $order) {
            $orderData = $ordersData->getById($order->getId());
            $parcelsData = $orderParcelsData->getById($order->getId());
            $shipments[] = Shipment::createFromOrderAndData($order, $orderData, $parcelsData, $shipStationAccount, $shippingAccount, $rootOu);
        }

        return new static(...$shipments);
    }
}