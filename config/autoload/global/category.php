<?php

use CG\Product\Category\StorageInterface as CategoryStorage;
use CG\Product\Category\Storage\Api as CategoryApiStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                CategoryStorage::class => CategoryApiStorage::class,
            ],
            CategoryApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
        ]
    ]
];