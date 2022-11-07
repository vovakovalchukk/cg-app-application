<?php
use CG\Shopify\Account\CreationService as ShopifyAccountCreator;
use CG\Shopify\Client\Factory as ClientFactory;
use Shopify\Account\Service as AccountService;
use Shopify\App\Service as AppService;
use Shopify\App\UserService as AppUserService;

return [
    'di' => [
        'instance' => [
            AccountService::class => [
                'parameters' => [
                    'cryptor' => 'shopify_cryptor',
                    'session' => 'shopify_session',
                ],
            ],
            ClientFactory::class => [
                'parameters' => [
                    'cryptor' => 'shopify_cryptor',
                ],
            ],
            ShopifyAccountCreator::class => [
                'parameters' => [
                    'cryptor' => 'shopify_cryptor',
                ],
            ],
            AppService::class => [
                'parameters' => [
                    'session' => 'shopify_session',
                ],
            ],
            AppUserService::class => [
                'parameters' => [
                    'predis' => 'reliable_redis',
                ],
            ],
        ],
    ]
];
