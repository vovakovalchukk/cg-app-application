<?php
namespace CG\RoyalMailApi\Shipment\Domestic;

use CG\RoyalMailApi\Shipment;

class NotApplicable extends Shipment
{
    const packageTypes = [
        'N' => 'Not Applicable'
    ];
}