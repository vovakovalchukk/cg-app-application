<?php

use CG\RoyalMailApi\DeliveryService\Service as DeliveryServiceService;

return [
    'di' => [
        'instance' => [
            DeliveryServiceService::class => [
                'parameters' => [
                    'servicesConfig' => [],
                    'defaultConfig' => []
                ]
            ]
        ]
    ]
];