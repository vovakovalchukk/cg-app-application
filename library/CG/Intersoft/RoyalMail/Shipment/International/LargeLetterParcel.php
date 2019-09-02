<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment\InternationalAbstract;

class LargeLetterParcel extends InternationalAbstract
{
    protected static $packageTypes = [
        'G' => 'Large Letter',
        'E' => 'Parcel',
    ];
}