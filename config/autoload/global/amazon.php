<?php
use CG\Amazon\ListingImport as AmazonListingImport;
use CG\Amazon\Region\Service as AmazonRegionService;

return [
    'di' => [
        'instance' => [
            AmazonRegionService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            AmazonListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'amazonGearmanClient'
                ]
            ]
        ],
    ],
];