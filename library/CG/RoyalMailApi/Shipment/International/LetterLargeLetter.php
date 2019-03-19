<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class LetterLargeLetter extends Shipment
{
    const packageTypes = [
        'P' => 'Letter',
        'G' => 'Large Letter',
    ];
}