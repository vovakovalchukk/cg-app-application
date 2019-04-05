<?php
namespace CG\Intersoft\RoyalMail\Shipment\Domestic;

use CG\Intersoft\RoyalMail\Shipment;

class NotApplicable extends Shipment
{
    protected static $packageTypes = [
        'N' => 'Not Applicable'
    ];
}