<?php
use BigCommerce\App\TokenService;
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
            TokenService::class => [
                'parameters' => [
                    'predis' => 'reliable_redis',
                ],
            ],
        ],
    ]
];
