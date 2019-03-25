<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class NotApplicable extends Shipment
{
    protected static $packageTypes = [
        'N' => 'Not Applicable'
    ];
}