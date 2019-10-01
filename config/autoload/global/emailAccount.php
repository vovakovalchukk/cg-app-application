<?php

use CG\EmailAccount\Storage\Api as EmailAccountApiStorage;
use CG\EmailAccount\StorageInterface as EmailAccountStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                EmailAccountStorage::class => EmailAccountApiStorage::class,
            ],
            EmailAccountApiStorage::class => [
                'parameter' => [
                    'client' => 'data-exchange-service_guzzle',
                ]
            ],
        ]
    ]
];