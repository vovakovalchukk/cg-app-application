<?php
namespace CG\ShipStation\Request\Shipping\Shipments;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Messages\Shipment;
use CG\ShipStation\Request\Shipping\Shipments as Request;
use CG\ShipStation\ShippingService\Factory as ShippingServiceFactory;
use CG\ShipStation\ShippingServiceInterface;

class Mapper
{
    /** @var ShippingServiceFactory */
    protected $shippingServiceFactory;

    public function __construct(ShippingServiceFactory $shippingServiceFactory)
    {
        $this->shippingServiceFactory = $shippingServiceFactory;
    }

    public function createFromOrdersAndData(
        OrderCollection $orders,
        OrderDataCollection $ordersData,
        OrderItemsDataCollection $orderItemsData,
        OrderParcelsDataCollection $orderParcelsData,
        Account $shipStationAccount,
        Account $shippingAccount,
        OrganisationUnit $rootOu
    ): Request {
        /** @var ShippingServiceInterface $shippingServiceService */
        $shippingServiceService = ($this->shippingServiceFactory)($shippingAccount);
        $shipments = [];
        foreach ($orders as $order) {
            /** @var OrderData $orderData */
            $orderData = $ordersData->getById($order->getId());
            $itemsData = $orderItemsData->getById($order->getId());
            $parcelsData = $orderParcelsData->getById($order->getId());
            $carrierService = $shippingServiceService->getCarrierService($orderData->getService());
            $shipments[] = Shipment::createFromOrderAndData(
                $order, $orderData, $itemsData, $parcelsData, $carrierService, $shipStationAccount, $shippingAccount, $rootOu
            );
        }

        return new Request(...$shipments);
    }
}