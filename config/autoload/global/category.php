<?php

use CG\Product\Category\ExternalData\Storage\Api as CategoryExternalStorageApi;
use CG\Product\Category\ExternalData\StorageInterface as CategoryExternalStorage;
use CG\Product\Category\Storage\Api as CategoryStorageApi;
use CG\Product\Category\StorageInterface as CategoryStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                CategoryStorage::class => CategoryStorageApi::class,
                CategoryExternalStorage::class => CategoryExternalStorageApi::class
            ],
            CategoryStorageApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            CategoryExternalStorageApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
        ]
    ]
];