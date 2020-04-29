<?php
use CG\BigCommerce\ListingImport as BigCommerceListingImport;
Use CG\BigCommerce\Gearman\Generator\ListingDownload as BigCommercListingDownloadGenerator;
use CG\BigCommerce\Gearman\Generator\Listing\CreateListing as BigCommercListingCreateListingGenerator;

return [
    'di' => [
        'instance' => [
            BigCommerceListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'bigcommerceGearmanClient'
                ]
            ],
            BigCommercListingDownloadGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'bigcommerceGearmanClient'
                ]
            ],
            BigCommercListingCreateListingGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'bigcommerceGearmanClient'
                ]
            ]
        ],
    ],
];