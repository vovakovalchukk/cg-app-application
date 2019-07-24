<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment\InternationalAbstract;

class Parcel extends InternationalAbstract
{
    protected static $packageTypes = [
        'E' => 'Parcel',
    ];
}