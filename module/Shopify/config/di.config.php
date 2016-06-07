<?php
use CG_Shopify\Account\Service as AccountService;

return [
    'di' => [
        'instance' => [
            AccountService::class => [
                'parameters' => [
                    'cryptor' => 'shopify_cryptor',
                ],
            ],
        ],
    ]
];
