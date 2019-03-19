<?php
namespace CG\RoyalMailApi\Shipment\Domestic;

use CG\RoyalMailApi\Shipment;

class LetterLargeLetter extends Shipment
{
    const packageTypes = [
        'L' => 'Letter',
        'F' => 'Large Letter',
    ];
}