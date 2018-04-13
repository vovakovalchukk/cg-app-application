<?php
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
        ],
    ],
];