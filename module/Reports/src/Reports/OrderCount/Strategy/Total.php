<?php
namespace Reports\OrderCount\Strategy;

use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Entity as Order;

class Total extends StrategyAbstract
{
    public function buildFromCollection(Orders $orders, string $unit, array $strategyType)
    {
        $counts = $this->createZeroFilledArrayForOrders($orders, $unit, $strategyType);
        /** @var Order $order */
        foreach ($orders as $order) {
            foreach ($strategyType as $type) {
                $typeKey = $this->getStrategyTypeKey($type);
                $unitKey = $this->unitService->formatUnitForEntityFromString($order->getPurchaseDate(), $unit);
                $current = isset($counts[$unitKey][$typeKey]) ? $counts[$unitKey][$typeKey] : 0;
                $counts[$unitKey][$typeKey] = $this->getNewValue($order, $type, $current);
            }
        }
        return $counts;
    }
}
