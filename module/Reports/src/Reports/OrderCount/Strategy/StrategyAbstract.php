<?php
namespace Reports\OrderCount\Strategy;

use CG\Order\Shared\Collection as Orders;
use CG\Stdlib\DateTime;
use Reports\OrderCount\UnitService;
use CG\Order\Shared\Entity as Order;
use Reports\OrderCount\Strategy\Type\Factory as TypeFactory;

abstract class StrategyAbstract implements StrategyInterface
{
    protected $unitService;
    protected $typeFactory;

    public function __construct(UnitService $unitService, TypeFactory $factory)
    {
        $this->unitService = $unitService;
        $this->typeFactory = $factory;
    }

    abstract function buildFromCollection(Orders $orders, string $unit, array $strategyType): array;

    protected function getLastOrder(Orders $orders): Order
    {
        foreach ($orders as $order) {
            // No-op, just moving the pointer to the end of the collection
        }
        /** @var Order $lastOrder */
        $lastOrder = $order;
        $orders->rewind();
        return $lastOrder;
    }

    protected function createZeroFilledArrayForOrders(Orders $orders, string $unit, array $typeKey)
    {
        $startDateTime = new DateTime($orders->getFirst()->getPurchaseDate());
        $endDateTime = new DateTime($this->getLastOrder($orders)->getPurchaseDate());
        return $this->unitService->createZeroFilledArray($startDateTime, $endDateTime, $unit, $typeKey);
    }

    protected function incrementWithTypeValue(Order $order, string $strategyType, $current)
    {
        return round($current + $this->typeFactory->getStrategyType($strategyType)->getIncreaseValue($order), 2);
    }

    protected function getStrategyTypeKey(string $strategyType)
    {
        return $this->typeFactory->getStrategyType($strategyType)->getKey();
    }
}
