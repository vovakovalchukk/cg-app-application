<?php
namespace CG\RoyalMailApi\Shipment\Domestic;

use CG\RoyalMailApi\Shipment;

class LargeLetter extends Shipment
{
    const packageTypes = [
        'F' => 'Large Letter'
    ];
}