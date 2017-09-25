<?php
namespace Reports\OrderCount;

use Reports\OrderCount\Strategy\Factory;
use CG\Order\Shared\Collection as Orders;

class Service
{
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function buildOrderCounts(Orders $orders, string $unit, array $strategies, array $strategyType)
    {
        $counts = [];
        foreach ($strategies as $strategy) {
            $counts[$strategy] = $this->factory->getStrategy($strategy)->buildFromCollection($orders, $unit, $strategyType);
        }
        return $counts;
    }
}
