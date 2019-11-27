<?php

use CG\Supplier\Storage\Api as SupplierStorageApi;
use CG\Supplier\StorageInterface as SupplierStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                SupplierStorage::class => SupplierStorageApi::class,
            ],
            SupplierStorageApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
        ]
    ]
];