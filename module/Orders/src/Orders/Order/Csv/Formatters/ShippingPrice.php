<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Collection as OrderCollection;
use Orders\Order\Csv\FormatterInterface;

class ShippingPrice implements FormatterInterface
{
    public function __invoke(OrderCollection $orders)
    {
        $column = [];
        foreach($orders as $order) {
            if($order->getItems()->count() === 0 || $order->getItems()->count() === 1) {
                $column[] = $order->getShippingPrice();
                continue;
            }

            for($i = 0; $i < $order->getItems()->count(); $i++) {
                $column[] = '';
            }
        }
        return $column;
    }
}
