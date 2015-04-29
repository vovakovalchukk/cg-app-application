<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Collection as OrderCollection;
use Orders\Order\Csv\FormatterInterface;

class TotalOrderDiscount implements FormatterInterface
{
    protected $forEachItem;

    public function __construct($forEachItem = true)
    {
        $this->forEachItem = $forEachItem;
    }

    public function __invoke(OrderCollection $orders)
    {
        $column = [];
        foreach($orders as $order) {
            $totalDiscount = $order->getTotalDiscount();
            if($order->getItems()->count() === 0) {
                $column[] = $totalDiscount;
                continue;
            }

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
        }
        return $column;
    }
}