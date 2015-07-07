<?php
namespace Orders\Order\Csv\Mapper\Formatter;

class GiftWrapPrice extends GiftWrapAbstract
{
    const FIELD_NAME = 'giftWrapPriceString';

    protected function getFieldName()
    {
        return static::FIELD_NAME;
    }
}
