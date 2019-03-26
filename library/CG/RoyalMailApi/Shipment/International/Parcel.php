<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class Parcel extends Shipment
{
    protected static $packageTypes = [
        'E' => 'Parcel',
    ];
}