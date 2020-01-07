<?php

use CG\Developer\Storage\Api as DeveloperStorageApi;
use CG\Developer\StorageInterface as DeveloperStorage;
use CG\Developer\Application\Storage\Api as DeveloperAppStorageApi;
use CG\Developer\Application\StorageInterface as DeveloperAppStorage;
use Partner\Account\AuthoriseService as PartnerAuthoriseService;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                DeveloperStorage::class => DeveloperStorageApi::class,
                DeveloperAppStorage::class => DeveloperAppStorageApi::class,
            ],
            DeveloperStorageApi::class => [
                'parameters' => [
                    'client' => 'developer-service_guzzle'
                ]
            ],
            DeveloperAppStorageApi::class => [
                'parameters' => [
                    'client' => 'developer-service_guzzle'
                ]
            ],
            PartnerAuthoriseService::class => [
                'parameters' => [
                    'cryptor' => 'partner_cryptor'
                ]
            ],
        ],
    ]
];