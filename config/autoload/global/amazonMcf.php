<?php

// Amazon MCF
use CG\Amazon\Mcf\FulfillmentStatus\Mapper as McfFulfillmentStatusMapper;
use CG\Amazon\Mcf\FulfillmentStatus\Repository as McfFulfillmentStatusRepository;
use CG\Amazon\Mcf\FulfillmentStatus\Storage\Cache as McfFulfillmentStatusStorageCache;
use CG\Amazon\Mcf\FulfillmentStatus\Storage\Db as McfFulfillmentStatusStorageDb;
use CG\Amazon\Mcf\FulfillmentStatus\StorageInterface as McfFulfillmentStatusStorage;
use CG\Amazon\Request\FulfillmentOutbound\Mapper as McfRequestFulfillmentOutboundMapper;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                McfFulfillmentStatusStorage::class => McfFulfillmentStatusRepository::class,
            ],
            McfFulfillmentStatusStorageDb::class => [
                'parameters' => [
                    'readSql' => 'amazonReadSql',
                    'fastReadSql' => 'amazonFastReadSql',
                    'writeSql' => 'amazonWriteSql',
                    'mapper' => McfFulfillmentStatusMapper::class,
                ]
            ],
            McfFulfillmentStatusRepository::class => [
                'parameters' => [
                    'storage' => McfFulfillmentStatusStorageCache::class,
                    'repository' => McfFulfillmentStatusStorageDb::class,
                ]
            ],
            McfRequestFulfillmentOutboundMapper::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ]
            ],
        ]
    ]
];

