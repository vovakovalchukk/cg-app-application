<?php
namespace Orders\Order\Csv\Formatters;

class GiftWrapType extends GiftWrapAbstract
{
    const FIELD_NAME = 'giftWrapType';

    protected function getFieldName()
    {
        return static::FIELD_NAME;
    }
}