<?php
namespace Reports\OrderCount\Strategy;

use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Entity as Order;
use CG\Stdlib\DateTime;

class Channel extends StrategyAbstract
{
    public function buildFromCollection(Orders $orders, string $unit, array $strategyType)
    {
        $counts = $this->createDefaultArrayByChannel(
            $this->getStartEndDatesByChannel($orders),
            $unit,
            $strategyType
        );

        /** @var Order $order */
        foreach ($orders as $order) {
            foreach ($strategyType as $type) {
                $typeKey = $this->getStrategyTypeKey($type);
                $unitKey = $this->unitService->formatUnitForEntityFromString($order->getPurchaseDate(), $unit);
                $current = isset($counts[$order->getChannel()][$unitKey][$typeKey]) ? $counts[$order->getChannel()][$unitKey][$typeKey] : 0;
                $counts[$order->getChannel()][$unitKey][$typeKey] = $this->getNewValue($order, $type, $current);
            }
        }

        return $counts;
    }

    protected function getStartEndDatesByChannel(Orders $orders)
    {
        $startEndDates = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            if (!isset($startEndDates[$order->getChannel()])) {
                $startEndDates[$order->getChannel()]['start'] = $order->getPurchaseDate();
                $startEndDates[$order->getChannel()]['end'] = $order->getPurchaseDate();
            } else {
                $startEndDates[$order->getChannel()]['end'] = $order->getPurchaseDate();
            }
        }
        return $startEndDates;
    }

    protected function createDefaultArrayByChannel(array $startEndDates, string $unit, array $typeKey)
    {
        $counts = [];
        foreach ($startEndDates as $channel => $dates) {
            $counts[$channel] = $this->unitService->createZeroFilledArray(
                new DateTime($dates['start']),
                new DateTime($dates['end']),
                $unit,
                $typeKey
            );
        }
        return $counts;
    }
}
