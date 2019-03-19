<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class Parcel extends Shipment
{
    const packageTypes = [
        'E' => 'Parcel',
    ];
}