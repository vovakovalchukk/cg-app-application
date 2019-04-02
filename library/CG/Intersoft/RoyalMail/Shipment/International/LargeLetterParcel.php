<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment;

class LargeLetterParcel extends Shipment
{
    protected static $packageTypes = [
        'G' => 'Large Letter',
        'E' => 'Parcel',
    ];
}