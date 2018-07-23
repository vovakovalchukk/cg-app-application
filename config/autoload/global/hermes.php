<?php

use CG\Hermes\DeliveryService\Service as DeliveryServiceService;

return [
    'di' => [
        'instance' => [
            DeliveryServiceService::class => [
                'parameters' => [
                    'servicesConfig' => [
                        'Standard' => [
                            'displayName' => 'Standard service',
                        ],
                        'NextDay' => [
                            'displayName' => 'Next Day',
                            'nextDay' => true,
                            'countries' => [
                                'AT', 'BE', 'CZ', 'DK', 'FI', 'FR', 'DE', 'HU', 'IT', 'LU', 'MC', 'NL', 'PT', 'SK', 'ES', 'SE', 'GB',
                            ]
                        ],
                        'Sunday' => [
                            'displayName' => 'Sunday service',
                            'specificDay' => 'sunday',
                        ]
                    ],
                    'defaultConfig' => [
                        'options' => [
                            'signature' => [
                                'countries' => [
                                    'AT', 'BE', 'CZ', 'DK', 'FI', 'FR', 'DE', 'HU', 'IT', 'LU', 'MC', 'NL', 'PT', 'SK', 'ES', 'SE', 'GB',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];