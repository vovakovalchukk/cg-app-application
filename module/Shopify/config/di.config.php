<?php
use CG_Shopify\Account\Service as AccountService;
use CG\Shopify\Client\Factory as ClientFactory;

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
        ],
    ]
];
