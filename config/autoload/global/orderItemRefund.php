<?php

use CG\Order\Client\Item\Refund\Storage\Api as ItemRefundApiStorage;
use CG\Order\Shared\Item\Refund\StorageInterface as ItemRefundStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                ItemRefundStorage::class => ItemRefundApiStorage::class,
            ],
            ItemRefundApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
        ]
    ]
];