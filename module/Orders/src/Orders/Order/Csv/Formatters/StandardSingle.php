<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\FormatterInterface;

class StandardSingle implements FormatterInterface
{
    public function __invoke(Order $order, $fieldName)
    {
        $getter = 'get' . ucfirst($fieldName);

        try {
            return $order->$getter();
        } catch (\BadMethodCallException $e) {
            return '';
        }
    }
}
