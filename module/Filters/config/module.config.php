<?php
use CG\Listing\Unimported\Marketplace\Storage\Api as UnimportedListingMarketplaceApiStorage;
use CG\Listing\Unimported\Marketplace\StorageInterface as UnimportedListingMarketplaceStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                UnimportedListingMarketplaceStorage::class => UnimportedListingMarketplaceApiStorage::class,
            ],
            UnimportedListingMarketplaceApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ],
            ],
        ],
    ],
];
