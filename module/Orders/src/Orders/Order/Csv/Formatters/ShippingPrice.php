<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\FormatterInterface;

class ShippingPrice implements FormatterInterface
{
    public function __invoke(Order $order)
    {
        $column = [];
        if($order->getItems()->count() < 2) {
            $column[] = $order->getShippingPrice();
        }

        for($i = 1; $i < $order->getItems()->count(); $i++) {
            $column[] = '';
        }

        return $column;
    }
}
