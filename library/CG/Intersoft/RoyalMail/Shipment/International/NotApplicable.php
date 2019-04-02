<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment;

class NotApplicable extends Shipment
{
    protected static $packageTypes = [
        'N' => 'Not Applicable'
    ];
}