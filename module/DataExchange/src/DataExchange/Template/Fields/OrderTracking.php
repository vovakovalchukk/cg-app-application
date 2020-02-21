<?php
namespace DataExchange\Template\Fields;

use DataExchange\Template\FieldsInterface;

class OrderTracking implements FieldsInterface
{
    const FIELDS = [
        'Order ID' => 'orderId',
        'Tracking number' => 'number',
        'Carrier' => 'carrier'
    ];

    public static function getFields(): array
    {
        return static::FIELDS;
    }
}
