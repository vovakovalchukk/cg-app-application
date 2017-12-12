<?php

use Opensoft\Rollout\Storage\RedisStorageAdapter as RolloutRedisStorage;
use Opensoft\Rollout\Storage\StorageInterface as RolloutStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                RolloutStorage::class => RolloutRedisStorage::class,
            ],
            RolloutRedisStorage::class => [
                'parameters' => [
                    'redis' => 'reliable_redis',
                ]
            ],
        ]
    ]
];