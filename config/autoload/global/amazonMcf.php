<?php

use CG\Amazon\Mcf\FulfillmentStatus\Storage\Api as McfFulfillmentStatusApiStorage;
use CG\Amazon\Mcf\FulfillmentStatus\StorageInterface as McfFulfillmentStatusStorage;
use CG\Amazon\Request\FulfillmentOutbound\Mapper as McfRequestFulfillmentOutboundMapper;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                McfFulfillmentStatusStorage::class => McfFulfillmentStatusApiStorage::class,
            ],
            McfFulfillmentStatusApiStorage::class => [
                'parameter' => [
                    'client' => 'amazon_guzzle',
                ]
            ],
            McfRequestFulfillmentOutboundMapper::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ]
            ],
        ]
    ]
];

