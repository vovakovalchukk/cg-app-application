<?php

use CG\Account\Policy\Storage\Api as AccountPolicyStorageApi;
use CG\Account\Policy\StorageInterface as AccountPolicyStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                AccountPolicyStorage::class => AccountPolicyStorageApi::class,
            ],
            AccountPolicyStorageApi::class => [
                'parameters' => [
                    'client' => 'account_guzzle'
                ]
            ]
        ]
    ]
];