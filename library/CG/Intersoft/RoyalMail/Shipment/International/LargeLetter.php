<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment\InternationalAbstract;

class LargeLetter extends InternationalAbstract
{
    protected static $packageTypes = [
        'G' => 'Large Letter',
    ];
}