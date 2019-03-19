<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class NotApplicable extends Shipment
{
    const packageTypes = [
        'N' => 'Not Applicable'
    ];
}