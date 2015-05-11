<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\Mapper\FormatterInterface;

class VatRate implements FormatterInterface
{
    public function __invoke(Order $order, $fieldName)
    {
        if($order->getItems()->count() === 0) {
            return [];
        }

        $column = [];
        foreach($order->getItems() as $item) {
            $column[] = ($item->getCalculatedTaxPercentage() * 100) . '%';
        }

        return $column;
    }
}
