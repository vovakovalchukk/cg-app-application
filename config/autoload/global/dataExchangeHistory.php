<?php

use CG\DataExchangeHistory\Storage\Api as DataExchangeHistoryApiStorage;
use CG\DataExchangeHistory\StorageInterface as DataExchangeHistoryStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                DataExchangeHistoryStorage::class => DataExchangeHistoryApiStorage::class,
            ],
            DataExchangeHistoryApiStorage::class => [
                'parameter' => [
                    'client' => 'data-exchange-service_guzzle',
                ]
            ],
        ]
    ]
];