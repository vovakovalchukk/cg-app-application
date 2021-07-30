<?php

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
                                'displayName' => 'Parcels Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '220',
                                'displayName' => 'Parcels Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '210',
                                'displayName' => 'Parcels Next Day - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '2',
                                'displayName' => 'Parcels Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '221',
                                'displayName' => 'Parcels Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '211',
                                'displayName' => 'Parcels Next Day 12:00 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '9',
                                'displayName' => 'Parcels Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '222',
                                'displayName' => 'Parcels Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '212',
                                'displayName' => 'Parcels Next Day 10:30 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '3',
                                'displayName' => 'Parcels Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '4',
                                'displayName' => 'Parcels Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '225',
                                'displayName' => 'Parcels Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '215',
                                'displayName' => 'Parcels Saturday - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '7',
                                'displayName' => 'Parcels Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '226',
                                'displayName' => 'Parcels Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '216',
                                'displayName' => 'Parcels Saturday 10:30 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '5',
                                'displayName' => 'Parcels Saturday 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '48',
                                'displayName' => 'Parcels 48Hr - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '72',
                                'displayName' => 'Parcels 48Hr + - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '97',
                                'displayName' => 'Pallets 24Hr - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '98',
                                'displayName' => 'Pallets 48Hr - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '101',
                                'displayName' => 'Worldwide Air - deliver to doorstep only - signature',
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '102',
                                'displayName' => 'DHL Parcel International - deliver to doorstep only - signature',
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '204',
                                'displayName' => 'International Road Economy - deliver to doorstep only - signature',
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '206',
                                'displayName' => 'DHL Parcel Connect - deliver to doorstep only - signature',
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '40',
                                'displayName' => 'Bagit Small 1kg Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '240',
                                'displayName' => 'Bagit Small 1kg Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '230',
                                'displayName' => 'Bagit Small 1kg Next Day - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '41',
                                'displayName' => 'Bagit Small 1kg Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '241',
                                'displayName' => 'Bagit Small 1kg Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '231',
                                'displayName' => 'Bagit Small 1kg Next Day 12:00 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '49',
                                'displayName' => 'Bagit Small 1kg Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '242',
                                'displayName' => 'Bagit Small 1kg Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '232',
                                'displayName' => 'Bagit Small 1kg Next Day 10:30 - leave safe - non signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '42',
                                'displayName' => 'Bagit Small 1kg Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '43',
                                'displayName' => 'Bagit Small 1kg Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '245',
                                'displayName' => 'Bagit Small 1kg Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '235',
                                'displayName' => 'Bagit Small 1kg Saturday - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '46',
                                'displayName' => 'Bagit Small 1kg Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '246',
                                'displayName' => 'Bagit Small 1kg Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '236',
                                'displayName' => 'Bagit Small 1kg Saturday 10:30 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '44',
                                'displayName' => 'Bagit Small 1kg Saturday 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '30',
                                'displayName' => 'Bagit Medium 2kg Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '250',
                                'displayName' => 'Bagit Medium 2kg Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '31',
                                'displayName' => 'Bagit Medium 2kg Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '251',
                                'displayName' => 'Bagit Medium 2kg Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '39',
                                'displayName' => 'Bagit Medium 2kg Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '252',
                                'displayName' => 'Bagit Medium 2kg Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '32',
                                'displayName' => 'Bagit Medium 2kg Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '33',
                                'displayName' => 'Bagit Medium 2kg Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '255',
                                'displayName' => 'Bagit Medium 2kg Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '36',
                                'displayName' => 'Bagit Medium 2kg Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '256',
                                'displayName' => 'Bagit Medium 2kg Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '34',
                                'displayName' => 'Bagit Medium 2kg Saturday 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '20',
                                'displayName' => 'Bagit Large 5kg Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '260',
                                'displayName' => 'Bagit Large 5kg Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '21',
                                'displayName' => 'Bagit Large 5kg Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '261',
                                'displayName' => 'Bagit Large 5kg Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '29',
                                'displayName' => 'Bagit Large 5kg Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '262',
                                'displayName' => 'Bagit Large 5kg Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '22',
                                'displayName' => 'Bagit Large 5kg Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '23',
                                'displayName' => 'Bagit Large 5kg Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '265',
                                'displayName' => 'Bagit Large 5kg Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '26',
                                'displayName' => 'Bagit Large 5kg Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '266',
                                'displayName' => 'Bagit Large 5kg Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '24',
                                'displayName' => 'Bagit Large 5kg Saturday 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '10',
                                'displayName' => 'Bagit XL 10kg Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '270',
                                'displayName' => 'Bagit XL 10kg Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '11',
                                'displayName' => 'Bagit XL 10kg Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '271',
                                'displayName' => 'Bagit XL 10kg Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '19',
                                'displayName' => 'Bagit XL 10kg Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '272',
                                'displayName' => 'Bagit XL 10kg Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '12',
                                'displayName' => 'Bagit XL 10kg Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '13',
                                'displayName' => 'Bagit XL 10kg Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '275',
                                'displayName' => 'Bagit XL 10kg Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '16',
                                'displayName' => 'Bagit XL 10kg Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '276',
                                'displayName' => 'Bagit XL 10kg Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '14',
                                'displayName' => 'Bagit XL 10kg Saturday 09:00 - deliver to doorstep only - signature',
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
                                'lastName' => 'Parcel -  Do not Deliver',
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
                        //@todo sent email to UkMail about scenario 2.2 and 2.3 why it is not working
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
//                        [
//                            'customerReference' => 'TEST-S2.3',
//                            'deliveryService' => '204',
//                            'invoiceNumber' => '6',
//                            'deliveredDutyPaid' => false,
//                            'deliveryAddress' => [
//                                'firstName' => 'Test',
//                                'lastName' => 'Parcel 6 - Do not Deliver',
//                                'line1' => 'Lietzenburger Strasse 20',
//                                'line2' => '',
//                                'line3' => '',
//                                'line4' => 'Bergisch Gladbach Bensberg',
//                                'line5' => 'Nordrhein-Westfalen',
//                                'postCode' => '51429',
//                                'country' => 'France',
//                                'ISOAlpha2CountryCode' => 'FR',
//                                'emailAddress' => 'scenario23@example.com',
//                                'phoneNumber' => '8513648722'
//                            ],
//                            'packages' => [
//                                [
//                                    'weight' => 2,
//                                    'length' => 0.75,
//                                    'width' => 0.40,
//                                    'height' => 0.55
//                                ]
//                            ]
//                        ],
                    ]
                ]
            ],
        ],
    ],
];