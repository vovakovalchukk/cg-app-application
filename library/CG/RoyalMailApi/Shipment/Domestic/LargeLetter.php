<?php
namespace CG\RoyalMailApi\Shipment\Domestic;

use CG\RoyalMailApi\Shipment;

class LargeLetter extends Shipment
{
    protected static $packageTypes = [
        'F' => 'Large Letter'
    ];
}