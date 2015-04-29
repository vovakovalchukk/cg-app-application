<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\FormatterInterface;

class TotalOrderDiscount implements FormatterInterface
{
    protected $forEachItem;

    public function __construct($forEachItem = true)
    {
        $this->forEachItem = $forEachItem;
    }

    public function __invoke(Order $order)
    {
        $totalDiscount = $order->getTotalDiscount();
        if($order->getItems()->count() === 0) {
            return [$totalDiscount];
        }

        $column = [];
        foreach($order->getItems() as $item) {
            $totalDiscount += (float) $item->getIndividualItemDiscountPrice() * (float) $item->getItemQuantity();
        }

        $noItems = $order->getItems()->count();
        if(!$this->forEachItem) {
            $noItems = 1;
        }

        for($i = 0; $i < $noItems; $i++) {
            $column[] = $totalDiscount;
        }

        return $column;
    }
}