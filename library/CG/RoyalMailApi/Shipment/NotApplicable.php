<?php
namespace CG\RoyalMailApi;

use CG\RoyalMailApi\Shipment\Package;
use CG\RoyalMailApi\DeliveryService;

class NotApplicable extends Shipment
{
    const packageTypes = [
        'N' => 'Not Applicable'
    ];
}