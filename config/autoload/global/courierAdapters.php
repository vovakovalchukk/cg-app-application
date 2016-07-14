<?php

use CG\CourierAdapter\Provider\Implementation\Service;

return [
    'di' => [
        'instance' => [
            Service::class => [
                'parameters' => [
                    'adapterImplementationsConfig' => [
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