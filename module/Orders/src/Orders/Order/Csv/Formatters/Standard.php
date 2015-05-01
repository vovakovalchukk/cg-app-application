<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\FormatterInterface;

class Standard implements FormatterInterface
{
    protected $fieldName;

    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    public function __invoke(Order $order)
    {
        $getter = 'get' . ucfirst($this->fieldName);
        $column = [];

        if($order->getItems()->count() === 0) {
            try {
                $column[] = $order->$getter();
            } catch (\BadMethodCallException $e) {
                $column[] = '';
            }
        }

        foreach($order->getItems() as $item) {
            try {
                $column[] = $order->$getter();
            } catch (\BadMethodCallException $e) {
                if(is_callable([$item, $getter])) {
                    $column[] = $item->$getter();
                } else {
                    $column[] = '';
                }
            }
        }

        return $column;
    }
}
