<?php

use CG\PurchaseOrder\StorageInterface;
use CG\PurchaseOrder\Storage\Api as ApiStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => ApiStorage::class
            ],
            ApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
        ]
    ]
];
