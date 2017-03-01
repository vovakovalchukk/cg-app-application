<?php

use CG\Locking\Service as LockingService;
use Orders\Courier\Label\CreateService as LabelCreateService;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'NonBlockingLockingService' => LockingService::class,
            ],
            'NonBlockingLockingService' => [
                'parameters' => [
                    'expireAfter' => 30,
                    'maxRetries' => 0,
                    'waitTime' => 0,
                ]
            ],
            LabelCreateService::class => [
                'parameters' => [
                    'lockingService' => 'NonBlockingLockingService',
                ]
            ],
        ]
    ]
];