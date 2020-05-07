<?php
use CG\BigCommerce\ListingImport as BigCommerceListingImport;
Use CG\BigCommerce\Gearman\Generator\ListingDownload as BigCommerceListingDownloadGenerator;
use CG\BigCommerce\Gearman\Generator\Listing\CreateListing as BigCommerceListingCreateListingGenerator;

return [
    'di' => [
        'instance' => [
            BigCommerceListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'bigcommerceGearmanClient'
                ]
            ],
            BigCommerceListingDownloadGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'bigcommerceGearmanClient'
                ]
            ],
            BigCommerceListingCreateListingGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'bigcommerceGearmanClient'
                ]
            ]
        ],
    ],
];