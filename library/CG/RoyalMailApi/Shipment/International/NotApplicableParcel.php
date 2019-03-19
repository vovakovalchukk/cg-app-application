<?php
namespace CG\RoyalMailApi\Shipment\International;

use CG\RoyalMailApi\Shipment;

class NotApplicableParcel extends Shipment
{
    const packageTypes = [
        'N' => 'Not Applicable',
        'G' => 'Large Letter',
    ];
}