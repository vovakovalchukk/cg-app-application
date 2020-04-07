<?php

use CG\Cache\StampedePrevention\Caching\Strategy\Runtime as RuntimeCachingStrategy;
use CG\Cache\StampedePrevention\Caching\StrategyInterface as CachingStrategyInterface;
use CG\Cache\StampedePrevention\Locking\Strategy\Standard as StandardLockingStrategy;
use CG\Cache\StampedePrevention\Locking\StrategyInterface as LockingStrategyInterface;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                LockingStrategyInterface::class => StandardLockingStrategy::class,
                CachingStrategyInterface::class => RuntimeCachingStrategy::class,
            ],
        ]
    ]
];