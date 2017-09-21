<?php
namespace Reports\OrderCount\Strategy\Type;

use CG\Order\Shared\Entity as Order;

interface TypeInterface
{
    public function getIncreaseValue(Order $order);
}
