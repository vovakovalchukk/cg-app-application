<?php

use CG\Ebay\Listing\CreateData\StorageInterface as ListingDataStorage;
use CG\Ebay\Listing\CreateData\Storage\Redis as ListingDataStorageRedis;
use CG\Ebay\ListingImport;

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
            ],
            ListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'ebayGearmanClient'
                ]
            ],
        ]
    ]
];