<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class LargeLetter extends Shipment
{
    protected static $packageTypes = [
        'G' => 'Large Letter',
    ];
}