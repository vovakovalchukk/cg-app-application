<?php

use CG\Courier\UkMail\DeliveryService\DisplayName as DeliveryServiceName;
use CG\UkMail\Authenticate\Service as AuthenticateService;
use CG\UkMail\Collection\Service as CollectionService;
use CG\UkMail\DeliveryService\Service as DeliveryServiceService;
use CG\UkMail\Credentials\Request\TestPackGenerator;

return [
    'di' => [
        'instance' => [
            AuthenticateService::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis'
                ],
            ],
            CollectionService::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis'
                ],
            ],
            DeliveryServiceService::class => [
                'parameters' => [
                    'servicesConfig' => [
                        'services' => [
                            [
                                'serviceCode' => '1',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_1,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '220',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_220,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '210',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_210,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '2',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_2,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '221',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_221,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '211',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_211,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '9',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_9,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '222',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_222,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '212',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_212,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '3',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_3,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '4',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_4,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '225',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_225,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '215',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_215,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '7',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_7,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '226',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_226,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '216',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_216,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '5',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_5,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '48',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_48,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '72',
                                'displayName' => DeliveryServiceName::UK_MAIL_PARCELS_72,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '97',
                                'displayName' => DeliveryServiceName::UK_MAIL_PALLETS_97,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '98',
                                'displayName' => DeliveryServiceName::UK_MAIL_PALLETS_98,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '101',
                                'displayName' => DeliveryServiceName::UK_MAIL_WORLDWIDE_AIR_101,
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '102',
                                'displayName' => DeliveryServiceName::UK_MAIL_INTL_102,
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '204',
                                'displayName' => DeliveryServiceName::UK_MAIL_INTL_204,
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '206',
                                'displayName' => DeliveryServiceName::UK_MAIL_INTL_206,
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '40',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_40,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '240',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_240,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '230',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_230,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '41',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_41,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '241',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_241,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '231',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_231,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '49',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_49,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '242',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_242,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '232',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_232,
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '42',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_42,
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '43',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_43,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '245',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_245,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '235',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_235,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '46',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_46,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '246',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_246,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '236',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_236,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '44',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_44,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '30',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_30,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '250',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_250,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '31',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_31,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '251',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_251,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '39',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_39,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '252',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_252,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '32',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_32,
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '33',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_33,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '255',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_255,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '36',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_36,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '256',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_256,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '34',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_34,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '20',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_20,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '260',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_260,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '21',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_21,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '261',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_261,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '29',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_29,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '262',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_262,
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '22',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_22,
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '23',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_23,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '265',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_265,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '26',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_26,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '266',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_266,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '24',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_24,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '10',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_10,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '270',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_270,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '11',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_11,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '271',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_271,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '19',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_19,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '272',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_272,
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '12',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_12,
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '13',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_13,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '275',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_275,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '16',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_16,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '276',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_276,
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '14',
                                'displayName' => DeliveryServiceName::UK_MAIL_BAGIT_14,
                                'domestic' => true,
                            ],
                        ],
                    ],
                ],
            ],
            TestPackGenerator::class => [
                'parameters' => [
                    'shipmentsData' => [
                        [
                            'customerReference' => 'TEST-S1.1',
                            'deliveryService' => '1',
                            'invoiceNumber' => '1',
                            'deliveredDutyPaid' => true,
                            'deliveryAddress' => [
                                'firstName' => 'Test',
                                'lastName' => 'Parcel - Do not Deliver',
                                'line1' => '145 Alfreton Road',
                                'line2' => 'Little Eaton',
                                'line3' => '',
                                'line4' => 'Derby',
                                'line5' => '',
                                'postCode' => 'DE21 5AA',
                                'emailAddress' => 'scenario11@example.com',
                                'phoneNumber' => '1213456714'
                            ],
                            'packages' => [
                                [
                                    'weight' => 0.95,
                                    'length' => 0.10,
                                    'width' => 0.15,
                                    'height' => 0.20
                                ]
                            ]
                        ],
                        [
                            'customerReference' => 'TEST-S1.2',
                            'deliveryService' => '48',
                            'invoiceNumber' => '2',
                            'deliveredDutyPaid' => true,
                            'deliveryAddress' => [
                                'firstName' => 'Jersey Post',
                                'lastName' => '',
                                'line1' => 'Postal Headquarters',
                                'line2' => '',
                                'line3' => '',
                                'line4' => 'JERSEY',
                                'line5' => 'Channel Islands',
                                'postCode' => 'JE1 1AA',
                                'emailAddress' => 'scenario12@example.com',
                                'phoneNumber' => '1213666714'
                            ],
                            'packages' => [
                                [
                                    'weight' => 1.45,
                                    'length' => 0.14,
                                    'width' => 0.19,
                                    'height' => 0.24
                                ]
                            ]
                        ],
                        [
                            'customerReference' => 'TEST-S1.3',
                            'deliveryService' => '48',
                            'invoiceNumber' => '3',
                            'deliveredDutyPaid' => true,
                            'deliveryAddress' => [
                                'firstName' => 'Newtownards Retail',
                                'lastName' => '',
                                'line1' => '241 Newtownards Road',
                                'line2' => '',
                                'line3' => '',
                                'line4' => 'Belfast',
                                'line5' => '',
                                'postCode' => 'BT4 1AF',
                                'emailAddress' => 'scenario13@example.com',
                                'phoneNumber' => '1213678714'
                            ],
                            'packages' => [
                                [
                                    'weight' => 1.25,
                                    'length' => 0.14,
                                    'width' => 0.45,
                                    'height' => 0.45
                                ]
                            ]
                        ],
                        [
                            'customerReference' => 'TEST-S2.1',
                            'deliveryService' => '101',
                            'invoiceNumber' => '4',
                            'deliveredDutyPaid' => false,
                            'deliveryAddress' => [
                                'firstName' => 'Test',
                                'lastName' => 'Parcel 4 - Do not Deliver',
                                'line1' => 'Lietzenburger Strasse 20',
                                'line2' => '',
                                'line3' => '',
                                'line4' => 'Bergisch Gladbach Bensberg',
                                'line5' => 'Nordrhein-Westfalen',
                                'postCode' => '51429',
                                'country' => 'Germany',
                                'ISOAlpha2CountryCode' => 'DE',
                                'emailAddress' => 'scenario21@example.com',
                                'phoneNumber' => '1213648722'
                            ],
                            'packages' => [
                                [
                                    'weight' => 2,
                                    'length' => 0.50,
                                    'width' => 0.60,
                                    'height' => 0.45,
                                    'contents' => [
                                        ['unitValue' => 100]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'customerReference' => 'TEST-S2.2',
                            'deliveryService' => '206',
                            'invoiceNumber' => '5',
                            'deliveredDutyPaid' => true,
                            'deliveryAddress' => [
                                'firstName' => 'Test',
                                'lastName' => 'Parcel 5 - Do not Deliver',
                                'line1' => 'Lietzenburger Strasse 20',
                                'line2' => '',
                                'line3' => '',
                                'line4' => 'Bergisch Gladbach Bensberg',
                                'line5' => 'Nordrhein-Westfalen',
                                'postCode' => '51429',
                                'country' => 'Germany',
                                'ISOAlpha2CountryCode' => 'DE',
                                'emailAddress' => 'scenario22@example.com',
                                'phoneNumber' => '1247648722'
                            ],
                            'packages' => [
                                [
                                    'weight' => 2,
                                    'length' => 0.20,
                                    'width' => 0.20,
                                    'height' => 0.20,
                                    'contents' => [
                                        ['unitValue' => 100]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'customerReference' => 'TEST-S2.3',
                            'deliveryService' => '204',
                            'invoiceNumber' => '6',
                            'deliveredDutyPaid' => true,
                            'deliveryAddress' => [
                                'firstName' => 'Test',
                                'lastName' => 'Parcel 6 - Do not Deliver',
                                'line1' => 'Lietzenburger Strasse 20',
                                'line2' => '',
                                'line3' => '',
                                'line4' => 'Bergisch Gladbach Bensberg',
                                'line5' => 'Nordrhein-Westfalen',
                                'postCode' => '51429',
                                'country' => 'Germany',
                                'ISOAlpha2CountryCode' => 'DE',
                                'emailAddress' => 'scenario23@example.com',
                                'phoneNumber' => '8513648722'
                            ],
                            'packages' => [
                                [
                                    'weight' => 2,
                                    'length' => 0.75,
                                    'width' => 0.40,
                                    'height' => 0.55,
                                    'contents' => [
                                        ['unitValue' => 200]
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ],
        ],
    ],
];