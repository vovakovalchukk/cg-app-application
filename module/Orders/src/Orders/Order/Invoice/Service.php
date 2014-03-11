<?php
namespace Orders\Order\Invoice;

use Zend\Di\Di;
use Orders\Order\Service as OrderService;
use CG\Order\Service\Filter;
use CG\Order\Shared\Collection;
use Exception;

class Service
{
    protected $di;
    protected $orderService;

    public function __construct(Di $di, OrderService $orderService)
    {
        $this->setDi($di)->setOrderService($orderService);
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }

    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @param array $orderIds
     * @return Response
     */
    public function getResponseFromOrderIds(array $orderIds)
    {
        $filter = $this->getDi()->get(Filter::class, ['id' => $orderIds]);
        $collection = $this->getOrderService()->getOrders($filter);
        return $this->getResponseFromOrderCollection($collection);
    }

    /**
     * @param Collection $orderCollection
     * @return Response
     */
    public function getResponseFromOrderCollection(Collection $orderCollection)
    {
        return $this->getDi()->get(
            Response::class,
            [
                'content' => ''
            ]
        );
    }
}