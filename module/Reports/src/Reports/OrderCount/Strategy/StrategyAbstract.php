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

    abstract function buildFromCollection(Orders $orders, string $unit, string $strategyType);

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

    protected function createZeroFilledArrayForOrders(Orders $orders, string $unit)
    {
        $startDateTime = new DateTime($orders->getFirst()->getPurchaseDate());
        $endDateTime = new DateTime($this->getLastOrder($orders)->getPurchaseDate());
        return $this->unitService->createZeroFilledArray($startDateTime, $endDateTime, $unit);
    }

    protected function getNewValue(Order $order, string $strategyType, $current)
    {
        return $current + $this->typeFactory->getStrategyType($strategyType)->getIncreaseValue($order);
    }
}
