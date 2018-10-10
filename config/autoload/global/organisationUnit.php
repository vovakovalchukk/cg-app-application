<?php

use CG\OrganisationUnit\Storage\Api as OrganisationUnitClient;
use CG\OrganisationUnit\StorageInterface as OrganisationUnitStorage;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitStorageApi;
use CG\OrganisationUnit\Storage\ApcRead as OrganisationUnitStorageApcRead;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                OrganisationUnitStorage::class => OrganisationUnitStorageApi::class,
            ],
            'aliases' => [
                'organisationUnitServiceApc' => OrganisationUnitService::class,
                'organisationUnitApcReadStorage' => OrganisationUnitStorageApcRead::class,
            ],
            OrganisationUnitService::class => [
                'parameters' => [
                    'repository' => OrganisationUnitStorageApi::class,
                ]
            ],

            OrganisationUnitStorageApi::class => [
                'parameters' => [
                    'client' => 'directory_guzzle',
                ]
            ],

            'organisationUnitServiceApc' => [
                'parameters' => [
                    'repository' => 'organisationUnitApcReadStorage',
                ]
            ],

            'organisationUnitApcReadStorage' => [
                'parameters' => [
                    'storage' => OrganisationUnitStorageApi::class,
                ]
            ],








//            OrganisationUnitClient::class => [
//                'parameter' => [
//                    'client' => 'directory_guzzle'
//                ]
//            ],


        ]
    ]
];