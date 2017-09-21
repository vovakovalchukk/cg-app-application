<?php
namespace Reports\OrderCount\Strategy\Type;

use CG\Order\Shared\Entity as Order;

class OrderValue implements TypeInterface
{
    public function getIncreaseValue(Order $order)
    {
        return (int) $order->getTotal();
    }
}
