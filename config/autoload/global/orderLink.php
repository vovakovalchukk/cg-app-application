<?php

use CG\Order\Shared\OrderLink\Storage\Api as OrderLinkApiStorage;
use CG\Order\Shared\OrderLink\StorageInterface as OrderLinkStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                OrderLinkStorage::class => OrderLinkApiStorage::class,
            ],
            OrderLinkApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
        ]
    ]
];