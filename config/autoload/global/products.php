<?php

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
            'preferences' => [
                VariationAttributeMapStorage::class => VariationAttributeMapApi::class,
            ]
        ]
    ]
];