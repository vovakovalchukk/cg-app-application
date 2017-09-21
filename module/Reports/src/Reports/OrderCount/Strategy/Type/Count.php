<?php
namespace Reports\OrderCount\Strategy\Type;

use CG\Order\Shared\Entity as Order;

class Count implements TypeInterface
{
    public function getIncreaseValue(Order $order)
    {
        return 1;
    }
}
