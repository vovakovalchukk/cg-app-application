<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Collection as OrderCollection;
use Orders\Order\Csv\FormatterInterface;

class LineTotal implements FormatterInterface
{
    public function __invoke(OrderCollection $orders)
    {
        $column = [];
        foreach($orders as $order) {
            if($order->getItems()->count() === 0) {
                $column[] = '';
                continue;
            }
            foreach($order->getItems() as $item) {
                $column[] = (float) $item->getItemQuantity() * (float) $item->getIndividualItemPrice();
            }
        }
        return $column;
    }
}
