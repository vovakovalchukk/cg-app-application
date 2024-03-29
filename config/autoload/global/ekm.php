<?php

use CG\Ekm\Client\Factory as EkmClientFactory;
use CG\Ekm\Client\Rest\AccessToken\Storage as EkmAccessTokenStorage;
use CG\Ekm\Client\Rest\RefreshToken\Storage as EkmRefreshTokenStorage;
use CG\Ekm\Client\Soap as EkmSoapClient;
use CG\Ekm\Gearman\Proxy\ImportTaxRates;
use CG\Ekm\Product\Downloader\Soap as EkmSoapProductDownloader;

return [
    'di' => [
        'instance' => [
            ImportTaxRates::class => [
                'parameters' => [
                    'gearmanClient' => 'ekmGearmanClient'
                ]
            ],
            EkmSoapClient::class => [
                'parameters' => [
                    'cryptor' => 'ekm_cryptor',
                ],
            ],
            EkmClientFactory::class => [
                'parameters' => [
                    'cryptor' => 'ekm_cryptor',
                ]
            ],
            EkmSoapProductDownloader::class => [
                'parameters' => [
                    'cryptor' => 'ekm_cryptor',
                ]
            ],
            EkmAccessTokenStorage::class => [
                'parameters' => [
                    'predisClient' => 'unreliable_redis',
                ]
            ],
            EkmRefreshTokenStorage::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis',
                ]
            ],
        ],
    ],
];