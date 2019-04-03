<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment;

class LargeLetter extends Shipment
{
    protected static $packageTypes = [
        'G' => 'Large Letter',
    ];
}