<?php
namespace DataExchange\Template\Fields;

use CG\OrganisationUnit\Entity as Ou;
use DataExchange\Template\FieldsInterface;

class Stock implements FieldsInterface
{
    protected const VAT_KEY = 'VAT ';
    protected const VAT_VALUE = 'vat';

    protected const FIELDS = [
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

    public static function getFields(Ou $ou): array
    {
        return array_merge(static::FIELDS, static::getDynamicFields($ou));
    }

    protected static function getDynamicFields(Ou $ou): array
    {
        return static::getOUVatFields($ou);
    }

    protected static function getOUVatFields(Ou $ou): array
    {
        $vatFields = [];
        foreach ($ou->getMemberState() as $memberState) {
            $key = static::VAT_KEY.$memberState;
            $value = static::VAT_VALUE.$memberState;
            $vatFields[$key] = $value;
        }
        return $vatFields;
    }
}
