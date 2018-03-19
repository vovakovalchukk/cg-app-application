<?php
use CG\Etsy\Account;
use Etsy\Account\Service as AccountService;
use Zend\Session\Container as Session;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'etsy_session' => Session::class,
            ],
            'etsy_session' => [
                'parameters' => [
                    'name' => 'etsy'
                ],
            ],
            Account::class => [
                'parameters' => [
                    'session' => 'etsy_session',
                ],
            ],
            AccountService::class => [
                'parameters' => [
                    'session' => 'etsy_session',
                ],
            ],
        ],
    ]
];
