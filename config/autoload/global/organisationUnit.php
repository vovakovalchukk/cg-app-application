<?php
use CG\OrganisationUnit\Repository as OrganisationUnitRepository;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\ApcRead as OrganisationUnitStorageApcRead;
use CG\OrganisationUnit\Storage\ApcWrite as OrganisationUnitStorageApcWrite;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitStorageApi;
use CG\OrganisationUnit\StorageInterface as OrganisationUnitStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                OrganisationUnitStorage::class => OrganisationUnitStorageApcWrite::class,
            ],
            'aliases' => [
                'organisationUnitApcReadService' => OrganisationUnitService::class,
                'organisationUnitApcReadRepository' => OrganisationUnitRepository::class
            ],
            OrganisationUnitRepository::class => [
                'storage' => OrganisationUnitStorageApcWrite::class,
                'repository' => OrganisationUnitStorageApi::class
            ],
            OrganisationUnitService::class => [
                'parameters' => [
                    'repository' => OrganisationUnitRepository::class,
                ]
            ],
            OrganisationUnitStorageApi::class => [
                'parameters' => [
                    'client' => 'directory_guzzle',
                ]
            ],
            'organisationUnitApcReadService' => [
                'parameters' => [
                    'repository' => 'organisationUnitApcReadRepository',
                ]
            ],
            'organisationUnitApcReadRepository' => [
                'parameters' => [
                    'storage' => OrganisationUnitStorageApcRead::class,
                    'repository' => OrganisationUnitStorageApi::class
                ]
            ],
        ]
    ]
];