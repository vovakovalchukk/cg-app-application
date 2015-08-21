<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use BadMethodCallException;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as OrderItem;
use Orders\Order\Csv\Mapper\FormatterInterface;

class Standard implements FormatterInterface
{
    public function __invoke(Order $order, $fieldName)
    {
        $columns = [];

        if($order->getItems()->count() === 0) {
            $callback = $this->getCallbackValueForField($fieldName, $order);
            $columns[] = $callback();
        }

        foreach($order->getItems() as $item) {
            $callback = $this->getCallbackValueForField($fieldName, $order, $item);
            $columns[] = $callback();
        }

        return $columns;
    }

    protected function getCallbackValueForField($fieldName, Order $order, OrderItem $orderItem = null)
    {
        if (is_array($fieldName)) {
            list($fieldName, $fieldType) = $fieldName;
            return $this->getCallbackValueForFieldType($fieldName, $fieldType, $order, $orderItem);
        }

        $getter = 'get' . ucfirst($fieldName);
        return function() use($getter, $order, $orderItem) {
            if (is_callable([$order, $getter])) {
                try {
                    return $order->{$getter}();
                } catch (BadMethodCallException $exception) {
                    // Ignore Error - Try order item
                }
            }

            if ($orderItem && is_callable([$orderItem, $getter])) {
                try {
                    return $orderItem->{$getter}();
                } catch (BadMethodCallException $exception) {
                    // Ignore Error - Return nothing
                }
            }

            return '';
        };
    }

    protected function getCallbackValueForFieldType($fieldName, $fieldType, Order $order, OrderItem $orderItem = null)
    {
        $getter = 'get' . ucfirst($fieldName);
        return function() use($getter, $fieldType, $order, $orderItem) {
            $objects = [
                'order' => $order,
                'item' => $orderItem,
            ];

            if (isset($objects[$fieldType]) && is_callable([$objects[$fieldType], $getter])) {
                try {
                    return $objects[$fieldType]->{$getter}();
                } catch (BadMethodCallException $exception) {
                    // Ignore Error - Return nothing
                }
            }

            return '';
        };
    }
}
