<?php

use CG\CourierAdapter\Provider\Service;

return [
    'di' => [
        'instance' => [
            Service::class => [
                'parameters' => [
                    'adaptersConfig' => [
                        /* Example:
                        [
                            'channelName' => 'example',
                            'displayName' => 'Example',
                            'courierInterfaceClosure' => function()
                            {
                                return new \ExampleAdapter\Courier();
                            }
                        ]
                        */
                    ]
                ]
            ]
        ]
    ]
];