<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class LargeLetterParcel extends Shipment
{
    const packageTypes = [
        'G' => 'Large Letter',
        'E' => 'Parcel',
    ];
}