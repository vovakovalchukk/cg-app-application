<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Collection as OrderCollection;
use Orders\Order\Csv\FormatterInterface;

abstract class GiftWrapAbstract implements FormatterInterface
{
    abstract protected function getFieldName();

    public function __invoke(OrderCollection $orders)
    {
        $getter = 'get' . ucfirst($this->getFieldName());
        $column = [];

        foreach($orders as $order) {
            if($order->getItems()->count() === 0) {
                $column[] = '';
                continue;
            }

            foreach ($order->getItems() as $item) {
                if($item->getGiftWraps() == null || $item->getGiftWraps()->count() === 0) {
                    $column[] = '';
                    continue;
                }

                $item->getGiftWraps()->rewind();
                $column[] = $item->getGiftWraps()->current()->$getter();
            }
        }

        return $column;
    }
}
