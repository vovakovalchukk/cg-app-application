<?php

use CG\Amazon\Thread\Additional\Storage\Api as ThreadAdditionalApiStorage;
use CG\Amazon\Thread\Additional\StorageInterface as ThreadAdditionalStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                ThreadAdditionalStorage::class => ThreadAdditionalApiStorage::class,
            ],
            ThreadAdditionalApiStorage::class => [
                'parameter' => [
                    'client' => 'amazon_guzzle',
                ]
            ],
        ]
    ]
];