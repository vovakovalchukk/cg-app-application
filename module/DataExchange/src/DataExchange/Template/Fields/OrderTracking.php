<?php
namespace DataExchange\Template\Fields;

use CG\OrganisationUnit\Entity as Ou;
use DataExchange\Template\FieldsInterface;

class OrderTracking implements FieldsInterface
{
    const FIELDS = [
        'CG Order ID' => 'orderId',
        'Tracking number' => 'number',
        'Carrier' => 'carrier',
        'Shipping Service' => 'shippingService',
    ];

    public static function getFields(Ou $ou): array
    {
        return static::FIELDS;
    }
}
