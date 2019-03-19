<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class LetterLargeLetterParcel extends Shipment
{
    const packageTypes = [
        'P' => 'Letter',
        'G' => 'Large Letter',
        'E' => 'Parcel',
    ];
}