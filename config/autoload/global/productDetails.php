<?php
use CG\Product\AccountDetail\Storage\Api as ProductAccountDetailApiStorage;
use CG\Product\AccountDetail\StorageInterface as ProductAccountDetailStorage;
use CG\Product\CategoryDetail\Storage\Api as ProductCategoryDetailApiStorage;
use CG\Product\CategoryDetail\StorageInterface as ProductCategoryDetailStorage;
use CG\Product\ChannelDetail\Storage\Api as ProductChannelDetailApiStorage;
use CG\Product\ChannelDetail\StorageInterface as ProductChannelDetailStorage;
use CG\Product\Detail\Storage\Api as ProductDetailApiStorage;
use CG\Product\Detail\StorageInterface as ProductDetailStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                ProductDetailStorage::class => ProductDetailApiStorage::class,
                ProductChannelDetailStorage::class => ProductChannelDetailApiStorage::class,
                ProductAccountDetailStorage::class => ProductAccountDetailApiStorage::class,
                ProductCategoryDetailStorage::class => ProductCategoryDetailApiStorage::class,
            ],
            ProductDetailApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ProductChannelDetailApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ProductAccountDetailApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ProductCategoryDetailApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
        ],
    ],
];