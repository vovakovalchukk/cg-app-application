<?php

use CG\DataExchangeTemplate\Storage\Api as DataExchangeTemplateApiStorage;
use CG\DataExchangeTemplate\StorageInterface as DataExchangeTemplateStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                DataExchangeTemplateStorage::class => DataExchangeTemplateApiStorage::class,
            ],
            DataExchangeTemplateApiStorage::class => [
                'parameter' => [
                    'client' => 'data-exchange-service_guzzle',
                ]
            ],
        ]
    ]
];