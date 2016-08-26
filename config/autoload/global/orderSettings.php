<?php

use CG\Settings\Order\StorageInterface as OrderSettingsStorage;
use CG\Settings\Order\Storage\Api as OrderSettingsApiStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                OrderSettingsStorage::class => OrderSettingsApiStorage::class,
            ],
            OrderSettingsApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
        ]
    ]
];