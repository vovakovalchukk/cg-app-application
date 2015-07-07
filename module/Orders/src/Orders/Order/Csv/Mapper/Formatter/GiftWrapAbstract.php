<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\Mapper\FormatterInterface;

abstract class GiftWrapAbstract implements FormatterInterface
{
    abstract protected function getFieldName();

    public function __invoke(Order $order, $fieldName)
    {
        $getter = 'get' . ucfirst($this->getFieldName());

        if($order->getItems()->count() === 0) {
            return [''];
        }

        $column = [];
        foreach ($order->getItems() as $item) {
            if($item->getGiftWraps() == null || $item->getGiftWraps()->count() === 0) {
                $column[] = '';
                continue;
            }

            $item->getGiftWraps()->rewind();
            $column[] = $item->getGiftWraps()->current()->$getter();
        }

        return $column;
    }
}
