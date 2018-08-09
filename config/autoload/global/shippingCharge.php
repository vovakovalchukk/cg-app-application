<?php

use CG\Billing\Shipping\Charge\Storage\Api as ShippingChargeApiStorage;
use CG\Billing\Shipping\Charge\StorageInterface as ShippingChargeStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                ShippingChargeStorage::class => ShippingChargeApiStorage::class,
            ],
            ShippingChargeApiStorage::class => [
                'parameter' => [
                    'client' => 'billing_guzzle',
                ]
            ],
        ]
    ]
];