<?php
use CG\Etsy\Account;
use CG\Etsy\Account\CreationService;
use CG\Etsy\Client\Factory as ClientFactory;
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
//            ClientFactory::class => [
//                'parameters' => [
//                    'cryptor' => 'etsy_cryptor',
//                ],
//            ],
            AccessTokenService::class => [
                'parameters' => [
                    'cryptor' => 'etsy_cryptor',
                ],
            ]
        ]
    ]
];