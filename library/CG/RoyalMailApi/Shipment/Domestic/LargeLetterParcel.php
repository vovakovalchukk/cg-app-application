<?php
namespace CG\RoyalMailApi\Shipment\Domestic;

use CG\RoyalMailApi\Shipment;

class LargeLetterParcel extends Shipment
{
    protected static $packageTypes = [
        'F' => 'Large Letter',
        'P' => 'Parcel',
    ];
}