<?php

use CG\DataExchange\FileContents\StorageInterface as DataExchangeFileStorage;
use CG\DataExchange\FileContents\Storage\S3 as DataExchangeFileStorageS3;
use CG\FileStorage\S3\Adapter as S3Adapter;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'DataExchangeS3Adapter' => S3Adapter::class,
            ],
            'preferences' => [
                DataExchangeFileStorage::class => DataExchangeFileStorageS3::class,
            ],
            'DataExchangeS3Adapter' => [
                'parameter' => [
                    'location' => function () { return DataExchangeFileStorageS3::BUCKET; }
                ]
            ],
            DataExchangeFileStorageS3::class => [
                'parameter' => [
                    's3Adapter' => 'DataExchangeS3Adapter',
                ]
            ],
        ]
    ]
];