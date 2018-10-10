<?php

use CG\Ekm\Gearman\Proxy\ImportTaxRates;

return [
    'di' => [
        'instance' => [
            ImportTaxRates::class => [
                'parameters' => [
                    'gearmanClient' => 'ekmGearmanClient'
                ]
            ]
        ],
    ],
];