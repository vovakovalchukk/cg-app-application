<?php
namespace Reports\OrderCount\Strategy;

use CG\Order\Shared\Collection as Orders;

class NullStrategy implements StrategyInterface
{
    public function buildFromCollection(Orders $orders, string $unit, array $strategyType): array
    {
        // Return an empty array
        return [];
    }
}
