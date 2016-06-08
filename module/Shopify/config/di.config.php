<?php
use CG\Shopify\Account\CreationService as ShopifyAccountCreator;
use CG\Shopify\Client\Factory as ClientFactory;
use CG_Shopify\Account\Service as AccountService;

return [
    'di' => [
        'instance' => [
            AccountService::class => [
                'parameters' => [
                    'cryptor' => 'shopify_cryptor',
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
