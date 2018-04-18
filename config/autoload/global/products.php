<?php

use CG\Product\Mapper as ProductMapper;
use CG\Product\Locking\Entity as LockingProduct;
use CG\Product\VariationAttributeMap\Storage\Api as VariationAttributeMapApi;
use CG\Product\VariationAttributeMap\StorageInterface as VariationAttributeMapStorage;

return [
    'di' => [
        'instance' => [
            VariationAttributeMapApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ProductMapper::class => [
                'parameters' => [
                    'entityClass' => function() { return LockingProduct::class; },
                ],
            ],
            'preferences' => [
                VariationAttributeMapStorage::class => VariationAttributeMapApi::class,
            ]
        ]
    ]
];