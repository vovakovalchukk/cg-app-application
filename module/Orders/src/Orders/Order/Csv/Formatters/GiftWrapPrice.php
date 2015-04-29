<?php
namespace Orders\Order\Csv\Formatters;

class GiftWrapPrice extends GiftWrapAbstract
{
    const FIELD_NAME = 'giftWrapPrice';

    protected function getFieldName()
    {
        return static::FIELD_NAME;
    }
}