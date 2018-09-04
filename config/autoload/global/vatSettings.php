<?php
use CG\Settings\Vat\StorageInterface as VatSettingsStorage;
use CG\Settings\Vat\Storage\Api as VatSettingsApiStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                VatSettingsStorage::class => VatSettingsApiStorage::class,
            ],
            VatSettingsApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ],
            ],
        ],
    ],
];