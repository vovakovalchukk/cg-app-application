<?php
namespace Orders\Courier;

use CG\Order\Shared\Collection as OrderCollection;

class Service extends ServiceAbstract
{
    /**
     * @return array shippingAccounts
     */
    public function getShippingAccountsForOrders(OrderCollection $orders)
    {
        $shippingAccounts = [];
        foreach ($orders as $order) {
            $shippingAccountsForOrder = $this->getShippingAccounts($order);
            $shippingAccounts = array_merge($shippingAccounts, $shippingAccountsForOrder->toArray());
        }

        return array_unique($shippingAccounts, SORT_REGULAR);
    }
}