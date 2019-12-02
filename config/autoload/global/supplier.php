<?php

use CG\Supplier\Storage\Api as SupplierApiStorage;
use CG\Supplier\StorageInterface as SupplierStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                SupplierStorage::class => SupplierApiStorage::class,
            ],
            SupplierApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
        ]
    ]
];