<?php
use CG\OrganisationUnit\Repository as OrganisationUnitRepository;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\ApcRead as OrganisationUnitStorageApcRead;
use CG\OrganisationUnit\Storage\ApcWrite as OrganisationUnitStorageApcWrite;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitStorageApi;

return [
    'di' => [
        'instance' => [
            'preferences' => [],
            'aliases' => [
                'organisationUnitApcReadService' => OrganisationUnitService::class,
                'organisationUnitApcReadRepository' => OrganisationUnitRepository::class
            ],
            OrganisationUnitStorageApi::class => [
                'parameters' => [
                    'client' => 'directory_guzzle',
                ]
            ],
            OrganisationUnitRepository::class => [
                'parameters' => [
                    'storage' => OrganisationUnitStorageApcWrite::class,
                    'repository' => OrganisationUnitStorageApi::class
                ]
            ],
            OrganisationUnitService::class => [
                'parameters' => [
                    'repository' => OrganisationUnitRepository::class,
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