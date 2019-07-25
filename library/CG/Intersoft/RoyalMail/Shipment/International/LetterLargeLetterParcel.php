<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment\InternationalAbstract;

class LetterLargeLetterParcel extends InternationalAbstract
{
    protected static $packageTypes = [
        'P' => 'Letter',
        'G' => 'Large Letter',
        'E' => 'Parcel',
    ];
}