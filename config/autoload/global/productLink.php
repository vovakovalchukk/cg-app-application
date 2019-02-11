<?php

use CG\Product\Csv\Link\Service as ProductCsvLinkService;
use CG\FileStorage\S3\Adapter as ProductLinkCsvS3Storage;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'ProductLinkS3FileImportAdapter' => ProductLinkCsvS3Storage::class,
            ],
            'ProductLinkS3FileImportAdapter' => [
                'parameters' => [
                    'location' => 'channelgrabber-reports',
                ],
            ],
            ProductCsvLinkService::class => [
                'parameters' => [
                    'exportFileStorage' => 'ProductLinkS3FileImportAdapter',
                    'environment' => function() { return ENVIRONMENT ;},
                ],
            ],
        ],
    ],
];