<?php

use CG\CourierAdapter\Provider\Adapter\Service;

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
                            'courierFactory' => function()
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