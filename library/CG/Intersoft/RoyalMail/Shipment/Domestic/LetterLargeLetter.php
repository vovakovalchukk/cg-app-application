<?php
namespace CG\Intersoft\RoyalMail\Shipment\Domestic;

use CG\Intersoft\RoyalMail\Shipment;

class LetterLargeLetter extends Shipment
{
    protected static $packageTypes = [
        'L' => 'Letter',
        'F' => 'Large Letter',
    ];
}