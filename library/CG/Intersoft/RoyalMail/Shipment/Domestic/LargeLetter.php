<?php
namespace CG\Intersoft\RoyalMail\Shipment\Domestic;

use CG\Intersoft\RoyalMail\Shipment;

class LargeLetter extends Shipment
{
    protected static $packageTypes = [
        'F' => 'Large Letter'
    ];
}