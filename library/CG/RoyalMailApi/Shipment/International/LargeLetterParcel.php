<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class LargeLetterParcel extends Shipment
{
    protected static $packageTypes = [
        'G' => 'Large Letter',
        'E' => 'Parcel',
    ];
}