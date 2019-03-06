<?php
use CG\Walmart\Account;
use CG\Walmart\Account\CreationService;
use CG\Walmart\Client\AccessToken\Storage as AccessTokenStorage;
use CG\Walmart\Client\Factory as ClientFactory;

return [
    'di' => [
        'instance' => [
            CreationService::class => [
                'parameters' => [
                    'cryptor' => 'walmart_cryptor',
                    'channelAccount' => Account::class,
                ],
            ],
            ClientFactory::class => [
                'parameters' => [
                    'cryptor' => 'walmart_cryptor',
                ],
            ],
            AccessTokenStorage::class => [
                'parameters' => [
                    'predisClient' => 'unreliable_redis'
                ]
            ],
        ]
    ]
];