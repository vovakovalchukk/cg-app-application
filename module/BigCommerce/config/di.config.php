<?php
use BigCommerce\Account;
use BigCommerce\Account\Session as BigCommerceAccountSession;
use BigCommerce\Controller\AccountController;
use CG\BigCommerce\Account as BigCommerceAccount;
use Zend\Session\Container as SessionContainer;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'bigcommerce_account_session' => SessionContainer::class,
            ],
            'preferences' => [
                BigCommerceAccount::class => Account::class,
            ],
            'bigcommerce_account_session' => [
                'parameters' => [
                    'name' => 'bigcommerce\\account'
                ],
            ],
            AccountController::class => [
                'parameters' => [
                    'cryptor' => 'bigcommerce_cryptor',
                ],
            ],
            BigCommerceAccountSession::class => [
                'parameters' => [
                    'sessionContainer' => 'bigcommerce_account_session'
                ],
            ],
        ],
    ]
];
