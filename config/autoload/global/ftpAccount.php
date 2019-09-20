<?php

use CG\FtpAccount\Storage\Api as FtpAccountApiStorage;
use CG\FtpAccount\StorageInterface as FtpAccountStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                FtpAccountStorage::class => FtpAccountApiStorage::class,
            ],
            FtpAccountApiStorage::class => [
                'parameter' => [
                    'client' => 'data-exchange-service_guzzle',
                ]
            ],
        ]
    ]
];