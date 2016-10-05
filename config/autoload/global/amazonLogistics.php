<?php

use CG\Amazon\Carrier\Service as AmazonCarrierService;
use CG\Amazon\ShippingService\Service as AmazonShippingServiceService;
use CG\Amazon\ShippingService\Storage\Api as AmazonShippingServiceApiStorage;
use CG\Amazon\ShippingService\StorageInterface as AmazonShippingServiceStorage;

return [
    'di' => [
        'instance' => [
            AmazonShippingServiceApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ],
            ],
            AmazonShippingServiceService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            AmazonCarrierService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            'preferences' => [
                AmazonShippingServiceStorage::class => AmazonShippingServiceApiStorage::class,
            ]
        ]
    ]
];