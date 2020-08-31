<?php

use CG\FileStorage\S3\Adapter as OrderTrackingS3FileAdapter;
use CG\Order\Client\Tracking\FileStorage\S3 as S3OrderTrackingFileStorage;
use CG\Order\Client\Tracking\FileStorage\StorageInterface as OrderTrackingFileStorage;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'OrderTrackingS3Adapter' => OrderTrackingS3FileAdapter::class,
            ],
            'preferences' => [
                OrderTrackingFileStorage::class => S3OrderTrackingFileStorage::class,
            ],
            'OrderTrackingS3Adapter' => [
                'parameter' => [
                    'location' => function () { return S3OrderTrackingFileStorage::BUCKET; }
                ]
            ],
            S3OrderTrackingFileStorage::class => [
                'parameter' => [
                    'adapter' => 'OrderTrackingS3Adapter',
                ]
            ],
        ]
    ]
];