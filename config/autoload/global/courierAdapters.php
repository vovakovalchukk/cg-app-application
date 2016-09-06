<?php

use CG\CourierAdapter\EmailClientInterface;
use CG\CourierAdapter\Provider\Implementation\Email\Client as EmailClient;
use CG\CourierAdapter\Provider\Implementation\Service;
use CG\CourierAdapter\Provider\Implementation\Storage\Redis as RedisStorage;
use CG\CourierAdapter\StorageInterface;

// Adapter implementations
use CG\Courier\Geopost\Dpd\Courier as DpdCourier;
use CG\Courier\Geopost\Interlink\Courier as InterlinkCourier;
use CG\Courier\Parcelforce\Courier as ParcelforceCourier;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => RedisStorage::class,
                EmailClientInterface::class => EmailClient::class,
            ],
            RedisStorage::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis',
                ]
            ],
            Service::class => [
                'parameters' => [
                    'adapterImplementationsConfig' => [
                        [
                            'channelName' => 'parcelforce-ca',
                            'displayName' => 'Parcelforce',
                            'courierFactory' => function()
                            {
                                $courier = new ParcelforceCourier();
                                return $courier;
                            }
                        ],
                        [
                            'channelName' => 'dpd-ca',
                            'displayName' => 'DPD',
                            'courierFactory' => function()
                            {
                                $courier = new DpdCourier();
                                return $courier;
                            }
                        ],
                        [
                            'channelName' => 'interlink-ca',
                            'displayName' => 'Interlink',
                            'courierFactory' => function()
                            {
                                $courier = new InterlinkCourier();
                                return $courier;
                            }
                        ],
                    ]
                ]
            ]
        ]
    ]
];