<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment\InternationalAbstract;

class NotApplicable extends InternationalAbstract
{
    protected static $packageTypes = [
        'N' => 'Not Applicable'
    ];
}