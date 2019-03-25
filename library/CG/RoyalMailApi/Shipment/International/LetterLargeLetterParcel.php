<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class LetterLargeLetterParcel extends Shipment
{
    protected static $packageTypes = [
        'P' => 'Letter',
        'G' => 'Large Letter',
        'E' => 'Parcel',
    ];
}