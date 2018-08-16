<?php

use CG\Ebay\Listing\CreateData\StorageInterface as ListingDataStorage;
use CG\Ebay\Listing\CreateData\Storage\Redis as ListingDataStorageRedis;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                ListingDataStorage::class => ListingDataStorageRedis::class,
            ],
            ListingDataStorageRedis::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis',
                ]
            ]
        ]
    ]
];