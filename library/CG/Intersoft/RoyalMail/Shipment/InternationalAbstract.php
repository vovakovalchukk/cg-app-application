<?php
namespace CG\Intersoft\RoyalMail\Shipment;

use CG\Intersoft\RoyalMail\Shipment;

class InternationalAbstract extends Shipment
{
    public static function isDomestic(): bool
    {
        return false;
    }
}