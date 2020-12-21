<?php
namespace DataExchange\Template\Fields;

use DataExchange\Template\FieldsInterface;

class Stock implements FieldsInterface
{
    const FIELDS = [
        'SKU' => 'sku',
        'Product Name' => 'name',
        'Total Stock' => 'quantity',
        'Available Stock' => 'available',
        'Cost Price' => 'costPrice',
        'Supplier' => 'supplier',
        'EAN' => 'ean',
        'UPC' => 'upc',
        'Brand' => 'brand',
        'MPN' => 'mpn',
        'ASIN' => 'asin',
        'ISBN' => 'isbn',
        'GTIN' => 'gtin',
        'HS Tariff Number' => 'hsTariffNumber',
        'Country of Manufacture' => 'countryOfManufacture'
    ];

    public static function getFields(): array
    {
        return static::FIELDS;
    }
}
