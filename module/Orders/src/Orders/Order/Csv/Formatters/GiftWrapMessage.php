<?php
namespace Orders\Order\Csv\Formatters;

class GiftWrapMessage extends GiftWrapAbstract
{
    const FIELD_NAME = 'giftWrapMessage';

    protected function getFieldName()
    {
        return static::FIELD_NAME;
    }
}