<?php
namespace CG\RoyalMailApi;

use CG\RoyalMailApi\Shipment\Package;
use CG\RoyalMailApi\DeliveryService;

class NotApplicable extends Shipment
{
    protected static $packageTypes = [
        'N' => 'Not Applicable'
    ];
}