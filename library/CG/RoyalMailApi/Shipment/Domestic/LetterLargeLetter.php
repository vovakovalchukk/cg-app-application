<?php
namespace CG\RoyalMailApi\Shipment\Domestic;

use CG\RoyalMailApi\Shipment;

class LetterLargeLetter extends Shipment
{
    protected static $packageTypes = [
        'L' => 'Letter',
        'F' => 'Large Letter',
    ];
}