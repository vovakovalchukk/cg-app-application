<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class LargeLetter extends Shipment
{
    const packageTypes = [
        'G' => 'Large Letter',
    ];
}