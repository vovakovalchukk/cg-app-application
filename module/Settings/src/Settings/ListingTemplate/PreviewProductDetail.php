<?php
namespace Settings\ListingTemplate;

use CG\Product\Detail\Entity as ProductDetail;

class PreviewProductDetail extends ProductDetail
{
    const WEIGHT = 42;
    const WIDTH = 0.7;
    const HEIGHT = 0.74;
    const LENGTH = 1.20;
    const DESCRIPTION = 'This five-piece garden furniture set, including four chairs and a dining table, is the ideal furnishing for any garden or patio. Stylish, high-backed chairs with generous, comfortable armrests make this a great place to serve dinner, enjoy a coffee or drinks, or simply relax in comfort and make the most of the summer with this fantastic set';
    const EAN = '5421085046166';
    const UPC = '89268500100';
    const ISBN = '9781781101544';
    const BRAND = 'Woodard';
    const MPN = '5GARDINSET';
    const PRICE = 129.99;
    const COST = 69.99;
    const CONDITION = 'New';

    public function __construct($organisationUnitId)
    {
        parent::__construct(
            $organisationUnitId,
            PreviewProduct::SKU,
            static::WEIGHT,
            static::WIDTH,
            static::HEIGHT,
            static::LENGTH,
            PHP_INT_MAX,
            static::DESCRIPTION,
            static::EAN,
            static::BRAND,
            static::MPN,
            null,
            static::PRICE,
            static::COST,
            static::CONDITION,
            [],
            static::UPC,
            static::ISBN,
            null,
            false
        );
    }
}