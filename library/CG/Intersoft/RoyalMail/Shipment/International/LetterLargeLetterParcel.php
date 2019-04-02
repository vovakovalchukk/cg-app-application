<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment;

class LetterLargeLetterParcel extends Shipment
{
    protected static $packageTypes = [
        'P' => 'Letter',
        'G' => 'Large Letter',
        'E' => 'Parcel',
    ];
}