<?php
namespace Reports\OrderCount\Strategy;

use CG\Order\Shared\Collection as Orders;

interface StrategyInterface
{
    public function buildFromCollection(Orders $orders, string $unit, string $strategyType);
}
