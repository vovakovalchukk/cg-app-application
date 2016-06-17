<?php
use CG\Shopify\Client\Factory as ClientFactory;
use CG\Shopify\Webhook\Account\CreationService as ShopifyAccountCreator;
use CG_Shopify\Account\Service as AccountService;
use Zend\Session\Container as Session;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'shopify_session' => Session::class,
            ],
            'shopify_session' => [
                'parameters' => [
                    'name' => 'shopify'
                ],
            ],
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
        ],
    ]
];
