<?php
namespace CG\Intersoft\RoyalMail\Shipment\Domestic;

use CG\Intersoft\RoyalMail\Shipment;

class LargeLetterParcel extends Shipment
{
    protected static $packageTypes = [
        'F' => 'Large Letter',
        'P' => 'Parcel',
    ];
}