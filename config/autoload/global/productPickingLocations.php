<?php
use CG\Product\PickingLocation\StorageInterface;
use CG\Product\PickingLocation\Storage\Api as ApiStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => ApiStorage::class,
            ],
            ApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ],
            ],
        ],
    ],
];