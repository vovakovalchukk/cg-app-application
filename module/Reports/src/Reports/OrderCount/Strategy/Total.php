<?php
namespace Reports\OrderCount\Strategy;

use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Entity as Order;

class Total extends StrategyAbstract
{
    public function buildFromCollection(Orders $orders, string $unit, string $strategyType)
    {
        $counts = $this->createZeroFilledArrayForOrders($orders, $unit);
        /** @var Order $order */
        foreach ($orders as $order) {
            $unitKey = $this->unitService->formatUnitForEntityFromString($order->getPurchaseDate(), $unit);
            $current = isset($counts[$unitKey]) ? $counts[$unitKey] : 0;
            $counts[$unitKey] = $this->getNewValue($order,$strategyType, $current);
        }
        return $counts;
    }
}
