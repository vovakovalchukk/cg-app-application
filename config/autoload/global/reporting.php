<?php

use CG\Reporting\Order\StorageInterface;
use CG\Reporting\Order\Storage\Api as ApiStorage;

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
