<?php

use CG\Partner\Storage\Code as PartnerStorageCode;
use CG\Partner\StorageInterface as PartnerStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                PartnerStorage::class => PartnerStorageCode::class,
            ],
        ],
    ]
];