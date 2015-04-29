<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\FormatterInterface;

class LineDiscount implements FormatterInterface
{
    public function __invoke(Order $order)
    {
        if($order->getItems()->count() === 0) {
            return [''];
        }

        $column = [];
        foreach($order->getItems() as $item) {
            $column[] = (float) $item->getItemQuantity() * (float) $item->getIndividualItemDiscountPrice();
        }

        return $column;
    }
}
