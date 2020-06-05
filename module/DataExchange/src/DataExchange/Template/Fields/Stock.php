<?php
namespace DataExchange\Template\Fields;

use DataExchange\Template\FieldsInterface;

class Stock implements FieldsInterface
{
    const FIELDS = [
        'SKU' => 'sku',
        'Product Name' => 'name',
        'Total Stock' => 'quantity',
        'Cost Price' => 'costPrice',
        'Supplier' => 'supplier'
    ];

    public static function getFields(): array
    {
        return static::FIELDS;
    }
}
