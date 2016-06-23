<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;

class VatNumber extends VatNumberSingle
{
    public function __invoke(Order $order, $fieldName)
    {
        $rows = max(1, count($order->getItems()));
        $vatNumber = parent::__invoke($order, $fieldName);
        return array_fill(0, $rows, $vatNumber);
    }
}
