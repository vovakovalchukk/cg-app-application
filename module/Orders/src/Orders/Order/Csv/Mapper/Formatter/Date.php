<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;

class Date extends DateSingle
{
    public function __invoke(Order $order, $fieldName)
    {
        $formattedDate = parent::__invoke($order, $fieldName);
        $rows = max(1, count($order->getItems()));
        return array_fill(0, $rows, $formattedDate);
    }
}
