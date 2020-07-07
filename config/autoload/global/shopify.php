<?php

use CG\Shopify\Client as ShopifyClient;
use CG\Shopify\Client\MessageFactory as ShopifyClientMessageFactory;
use GuzzleHttp\Client as ShopifyGuzzleClient;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'CGShopifyGuzzleClient' => ShopifyGuzzleClient::class
            ],
            'CGShopifyGuzzleClient' => [
                'parameters' => [
                    'config' => [
                        'message_factory' => new ShopifyClientMessageFactory()
                    ]
                ]
            ],
            ShopifyClient::class => [
                'parameters' => [
                    'client' => 'CGShopifyGuzzleClient'
                ]
            ]
        ],
    ]
];