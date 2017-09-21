<?php
namespace Reports\OrderCount\Strategy;

use CG\Order\Shared\Collection as Orders;

class NullStrategy implements StrategyInterface
{
    public function buildFromCollection(Orders $orders, string $unit)
    {
        // No-op
    }
}
