<?php

use CG\Product\Csv\Link\Service as ProductCsvLinkService;
use CG\FileStorage\S3\Adapter as ProductLinkCsvS3Storage;
use CG\Channel\Product\Gearman\Generator\ImportLinks\Csv as ImportCsvLinksJobGenerator;
use CG\Product\Link\Gearman\Generator\ExportProductLinks as ExportCsvLinksJobGenerator;

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
            ImportCsvLinksJobGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'productGearmanClient',
                ],
            ],
            ExportCsvLinksJobGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'productGearmanClient',
                ],
            ],
        ],
    ],
];