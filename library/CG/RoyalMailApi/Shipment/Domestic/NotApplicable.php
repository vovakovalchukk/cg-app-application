<?php
namespace CG\RoyalMailApi\Shipment\Domestic;

use CG\RoyalMailApi\Shipment;

class NotApplicable extends Shipment
{
    protected static $packageTypes = [
        'N' => 'Not Applicable'
    ];
}