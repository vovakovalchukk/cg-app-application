<?php

use CG\ManualOrder\Account\CreationService as ManualOrderAccountCreationService;

return [
    'di' => [
        'instance' => [
            ManualOrderAccountCreationService::class => [
                'parameter' => [
                    'cryptor' => 'manual_cryptor',
                ]
            ],
        ]
    ]
];