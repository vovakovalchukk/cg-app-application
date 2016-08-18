<?php
use BigCommerce\App\UserService;
use BigCommerce\Controller\AccountController;

return [
    'di' => [
        'instance' => [
            AccountController::class => [
                'parameters' => [
                    'cryptor' => 'bigcommerce_cryptor',
                ],
            ],
            UserService::class => [
                'parameters' => [
                    'predis' => 'reliable_redis',
                ],
            ],
        ],
    ]
];
