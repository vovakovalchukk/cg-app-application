<?php

use CG\Amazon\BrowseNode\Category\Usage\Storage as BrowseNodeCategoryUsageStorage;
use CG\Amazon\Category\Storage\Api as AmazonCategoryStorageApi;
use CG\Amazon\Category\StorageInterface as AmazonCategoryStorage;
use CG\Amazon\Category\VariationTheme\Storage\Api as AmazonVariationThemeStorageApi;
use CG\Amazon\Category\VariationTheme\StorageInterface as AmazonVariationThemeStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                AmazonCategoryStorage::class => AmazonCategoryStorageApi::class,
                AmazonVariationThemeStorage::class => AmazonVariationThemeStorageApi::class,
            ],
            AmazonCategoryStorageApi::class => [
                'parameter' => [
                    'client' => 'amazon_guzzle',
                ]
            ],
            AmazonVariationThemeStorageApi::class => [
                'parameter' => [
                    'client' => 'amazon_guzzle',
                ]
            ],
            BrowseNodeCategoryUsageStorage::class => [
                'parameter' => [
                    'predis' => 'reliable_redis'
                ]
            ],
        ]
    ]
];