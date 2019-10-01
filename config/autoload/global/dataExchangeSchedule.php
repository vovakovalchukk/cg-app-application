<?php

use CG\DataExchangeSchedule\Storage\Api as DataExchangeScheduleApiStorage;
use CG\DataExchangeSchedule\StorageInterface as DataExchangeScheduleStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                DataExchangeScheduleStorage::class => DataExchangeScheduleApiStorage::class,
            ],
            DataExchangeScheduleApiStorage::class => [
                'parameter' => [
                    'client' => 'data-exchange-service_guzzle',
                ]
            ],
        ]
    ]
];