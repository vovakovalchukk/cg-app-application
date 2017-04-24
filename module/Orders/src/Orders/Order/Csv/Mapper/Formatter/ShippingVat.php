<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\Mapper\FormatterInterface;

class ShippingVat implements FormatterInterface
{
    public function __invoke(Order $order, $fieldName)
    {
        $column = [];

        $column[] = $order->getShippingTaxString();
        for($i = 1; $i < $order->getItems()->count(); $i++) {
            $column[] = '0.00';
        }

        return $column;
    }
}
