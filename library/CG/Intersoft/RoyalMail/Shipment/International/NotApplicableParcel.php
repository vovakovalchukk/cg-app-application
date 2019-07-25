<?php
namespace CG\Intersoft\RoyalMail\Shipment\International;

use CG\Intersoft\RoyalMail\Shipment\InternationalAbstract;

class NotApplicableParcel extends InternationalAbstract
{
    protected static $packageTypes = [
        'N' => 'Not Applicable',
        'G' => 'Large Letter',
    ];
}