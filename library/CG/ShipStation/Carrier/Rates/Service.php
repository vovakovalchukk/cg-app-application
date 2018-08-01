<?php
namespace CG\ShipStation\Carrier\Rates;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates\Collection as ShippingRateCollection;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates as OrderShippingRates;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Service
{
    public function fetchRatesForOrders(
        OrderCollection $orders,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $ordersParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        OrganisationUnit $rootOu,
        Account $shippingAccount
    ): ShippingRateCollection {
        // Dummy data to be replaced by TAC-125
        $dummyData = [
            [
                'rate_id' => 'se-12345',
                'shipping_amount' => [
                    'currency' => 'usd',
                    'amount' => 9.37
                ],
                'service_type' => 'USPS Test 1',
                'service_code' => 'usps_test_1',
            ],
            [
                'rate_id' => 'se-67890',
                'shipping_amount' => [
                    'currency' => 'usd',
                    'amount' => 10.50
                ],
                'service_type' => 'USPS Test 2',
                'service_code' => 'usps_test_2',
            ],
        ];
        $rates = new ShippingRateCollection();
        foreach ($orders as $order) {
            $orderRates = new OrderShippingRates($order->getId());
            foreach ($dummyData as $dummyRate) {
                $rate = Entity::fromShipEngineRateData($dummyRate);
                $orderRates->attach($rate);
            }
            $rates->attach($orderRates);
        }
        return $rates;
    }
}