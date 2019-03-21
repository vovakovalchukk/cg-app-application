<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class NotApplicableParcel extends Shipment
{
    protected static $packageTypes = [
        'N' => 'Not Applicable',
        'G' => 'Large Letter',
    ];
}