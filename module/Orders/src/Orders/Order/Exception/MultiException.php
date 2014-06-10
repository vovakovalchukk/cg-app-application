<?php
namespace Orders\Order\Exception;

use Exception;
use IteratorAggregate;
use ArrayIterator;
use Countable;

class MultiException extends Exception implements IteratorAggregate, Countable
{
    protected $orderExceptions = [];

    public function addOrderException($orderId, Exception $exception)
    {
        $this->orderExceptions[$orderId] = $exception;
    }

    public function getOrderIds()
    {
        return array_keys($this->getOrderExceptions());
    }

    public function getOrderExceptions()
    {
        return $this->orderExceptions;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getOrderExceptions());
    }

    public function count()
    {
        return count($this->getOrderExceptions());
    }
} 