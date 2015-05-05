<?php
namespace Orders\Order\Csv\Mapper\Formatter;

class GiftWrapType extends GiftWrapAbstract
{
    const FIELD_NAME = 'giftWrapType';

    protected function getFieldName()
    {
        return static::FIELD_NAME;
    }
}