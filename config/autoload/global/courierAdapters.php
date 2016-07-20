<?php

use CG\CourierAdapter\Provider\Implementation\Service;
use CG\CourierAdapter\Provider\Implementation\Storage\Redis as RedisStorage;
use CG\CourierAdapter\StorageInterface;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => RedisStorage::class,
            ],
            RedisStorage::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis',
                ]
            ],
            Service::class => [
                'parameters' => [
                    'adapterImplementationsConfig' => [
                        /* Example:
                        [
                            'channelName' => 'example',
                            'displayName' => 'Example',
                            'courierFactory' => function()
                            {
                                return new \ExampleAdapter\Courier();
                            }
                        ]
                        */
                    ]
                ]
            ]
        ]
    ]
];