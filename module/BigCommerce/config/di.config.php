<?php
use BigCommerce\Controller\AccountController;

return [
    'di' => [
        'instance' => [
            AccountController::class => [
                'parameters' => [
                    'cryptor' => 'bigcommerce_cryptor',
                ],
            ],
        ],
    ]
];
