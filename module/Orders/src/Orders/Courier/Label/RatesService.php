<?php
namespace Orders\Courier\Label;

use CG\Channel\Shipping\Provider\Service\ShippingRate\Collection as ShippingRateCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;

class RatesService extends ServiceAbstract
{
    public function fetchRates(
        array $orderIds,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $ordersParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        int $shippingAccountId
    ): ShippingRateCollection {
        // TODO
    }
}