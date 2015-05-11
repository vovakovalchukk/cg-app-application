<?php
namespace Orders\Order\Csv\Mapper\Formatter;

class GiftWrapMessage extends GiftWrapAbstract
{
    const FIELD_NAME = 'giftWrapMessage';

    protected function getFieldName()
    {
        return static::FIELD_NAME;
    }
}