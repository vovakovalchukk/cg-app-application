<?php
namespace Orders\Order\Csv\Mapper\Formatter;

class GiftWrapPrice extends GiftWrapAbstract
{
    const FIELD_NAME = 'giftWrapPrice';

    protected function getFieldName()
    {
        return static::FIELD_NAME;
    }
}