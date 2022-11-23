<?php
use CG\Etsy\Account;
use CG\Etsy\Account\CreationService;
use CG\Etsy\Client\AccessToken\Service as AccessTokenService;

return [
    'di' => [
        'instance' => [
            CreationService::class => [
                'parameters' => [
                    'cryptor' => 'etsy_cryptor',
                    'channelAccount' => Account::class,
                ],
            ],
            AccessTokenService::class => [
                'parameters' => [
                    'cryptor' => 'etsy_cryptor',
                ],
            ]
        ]
    ]
];