<?php
namespace CG\RoyalMailApi\Shipment\Domestic;

use CG\RoyalMailApi\Shipment;

class LetterLargeLetterParcel extends Shipment
{
    const packageTypes = [
        'L' => 'Letter',
        'F' => 'Large Letter',
        'P' => 'Parcel',
    ];
}