<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment;

class Parcel extends Shipment
{
    protected static $packageTypes = [
        'E' => 'Parcel',
    ];
}