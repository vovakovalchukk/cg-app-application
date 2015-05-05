<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\Mapper\FormatterInterface;

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
