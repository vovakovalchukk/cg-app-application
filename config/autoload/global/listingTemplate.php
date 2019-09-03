<?php

use CG\Listing\Template\Storage\Api as ListingTemplateApiStorage;
use CG\Listing\Template\StorageInterface as ListingTemplateStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                ListingTemplateStorage::class => ListingTemplateApiStorage::class,
            ],
            ListingTemplateApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
        ]
    ]
];