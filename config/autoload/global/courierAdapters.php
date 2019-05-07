<?php

use CG\CourierAdapter\EmailClientInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Email\Client as EmailClient;
use CG\CourierAdapter\Provider\Implementation\Service;
use CG\CourierAdapter\Provider\Implementation\Storage\Redis as RedisStorage;
use CG\CourierAdapter\StorageInterface;
use Zend\Di\Di;

// Adapter implementations
use CG\Courier\Geopost\Dpd\Courier as DpdCourier;
use CG\Courier\Geopost\Interlink\Courier as InterlinkCourier;
use CG\Courier\Parcelforce\Courier as ParcelforceCourier;
use CG\Courier\MyHermes\Courier as MyHermesCourier;
use CG\Hermes\CourierAdapter as HermesCorporateCourier;
use CG\RoyalMailApi\CourierAdapter as RoyalMailApiCourier;
use CG\Intersoft\RoyalMail\CourierAdapter as RoyalMailIntersoftCourier;

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
            CAAccountMapper::class => [
                'parameters' => [
                    'cryptor' => 'courieradapter_cryptor'
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
                            'courierFactory' => function(Di $di)
                            {
                                return $di->get(DpdCourier::class);
                            }
                        ],
                        [
                            'channelName' => 'interlink-ca',
                            'displayName' => 'DPD Local',
                            'courierFactory' => function(Di $di)
                            {
                                return $di->get(InterlinkCourier::class);
                            }
                        ],
                        [
                            'channelName' => 'myhermes-ca',
                            'displayName' => 'MyHermes',
                            'courierFactory' => function()
                            {
                                $courier = new MyHermesCourier();
                                return $courier;
                            }
                        ],
                        [
                            'channelName' => 'hermes-ca',
                            'displayName' => 'Hermes',
                            'featureFlag' => HermesCorporateCourier::FEATURE_FLAG,
                            'courierFactory' => function(Di $di)
                            {
                                return $di->get(HermesCorporateCourier::class);
                            }
                        ],
                        [
                            'channelName' => 'royal-mail-ca',
                            'displayName' => 'Royal Mail OBA (API)',
                            'featureFlag' => RoyalMailApiCourier::FEATURE_FLAG,
                            'courierFactory' => function(Di $di)
                            {
                                return $di->get(RoyalMailApiCourier::class);
                            }
                        ],
                        [
                            'channelName' => 'royal-mail-intersoft-ca',
                            'displayName' => 'Royal Mail (OBA)',
                            'featureFlag' => RoyalMailIntersoftCourier::FEATURE_FLAG,
                            'courierFactory' => function(Di $di)
                            {
                                return $di->get(RoyalMailIntersoftCourier::class);
                            }
                        ],
                    ]
                ]
            ]
        ]
    ]
];
