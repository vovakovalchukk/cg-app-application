<?php

use CG\FtpAccount\PasswordCryptor;
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
            PasswordCryptor::class => [
                'parameters' => [
                    'cryptor' => 'ftpaccount_cryptor',
                ]
            ]
        ]
    ]
];