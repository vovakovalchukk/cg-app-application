<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment;

class NotApplicableParcel extends Shipment
{
    protected static $packageTypes = [
        'N' => 'Not Applicable',
        'G' => 'Large Letter',
    ];
}