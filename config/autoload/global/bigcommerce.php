<?php
use CG\BigCommerce\ListingImport as BigCommerceListingImport;

return [
    'di' => [
        'instance' => [
            BigCommerceListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'bigcommerceGearmanClient'
                ]
            ]
        ],
    ],
];