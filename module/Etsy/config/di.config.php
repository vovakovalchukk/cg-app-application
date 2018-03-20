<?php
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
            AccountService::class => [
                'parameters' => [
                    'session' => 'etsy_session',
                ],
            ],
        ],
    ]
];
