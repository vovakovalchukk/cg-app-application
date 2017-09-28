<?php
namespace Reports\OrderCount\Strategy\Type;

use CG\Order\Shared\Entity as Order;

class Count implements TypeInterface
{
    const KEY = 'count';

    public function getIncreaseValue(Order $order)
    {
        return 1;
    }

    public function getKey()
    {
        return static::KEY;
    }
}
