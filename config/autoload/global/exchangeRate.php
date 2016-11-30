<?php

use CG\ExchangeRate\Storage\Api as ExchangeRateApiStorage;
use CG\ExchangeRate\StorageInterface as ExchangeRateStorage;

return [
    'di' => [
        'instance' => [
            ExchangeRateApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            'preferences' => [
                ExchangeRateStorage::class => ExchangeRateApiStorage::class,
            ]
        ]
    ]
];