<?php

use CG\NetDespatch\Account\CreationService as NetDespatchAccountCreationService;
use CG\NetDespatch\Order\CreateService as NetDespatchOrderCreateService;
use CG\NetDespatch\ShippingOptionsProvider as NetDespatchShippingOptionsProvider;
use CG\NetDespatch\ShippingService as NetDespatchShippingService;

return [
    'di' => [
        'instance' => [
            NetDespatchShippingService::class => [
                'parameters' => [
                    'defaultDomesticServices' => [
                        // These codes are prefixes, more characters will be added based on chosen options
                        '24' => '24',
                        '48' => '48',
                        '24F' => '24 (Flat)',
                        '48F' => '48 (Flat)',
                        'TPN01' => '24 Tracked',
                        'TPS01' => '48 Tracked',
                        'RMSD9' => 'Special Delivery 9am',
                        'RMSD1' => 'Special Delivery 1pm',
                        'STL1'  => '1st Class (STL)',
                        'STL2'  => '2nd Class (STL)',
                    ],
                    'defaultInternationalServices' => [
                        'OL' => 'International Economy',
                        'OS' => 'International Standard',
                        'OT' => 'International Tracked',
                    ]
                ]
            ],
            NetDespatchShippingOptionsProvider::class => [
                'parameters' => [
                    'carrierBookingOptions' => [
                        'parcels' => false,
                        'collectionDate' => false,
                        'weight' => true,
                        'packageType' => true,
                        'addOns' => true,
                        'height' => false,
                        'width' => false,
                        'length' => false,
                        'tradeTariffCode' => false,
                        'insurance' => false,
                        'insuranceMonetary' => false,
                        'signature' => false,
                        'deliveryInstructions' => true,
                        'itemParcelAssignment' => false,
                    ],
                    'serviceOptions' => [
                        '24' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        '48' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        '24F' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        '48F' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        'TPN01' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature', 'excludes' => 'Safe Place'],
                                ['title' => 'Safe Place', 'excludes' => 'Signature'],
                            ]
                        ],
                        'TPS01' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature', 'excludes' => 'Safe Place'],
                                ['title' => 'Safe Place', 'excludes' => 'Signature'],
                            ]
                        ],
                        'RMSD1' => [
                            'packageTypes' => ['Parcel'],
                            'addOns' => [
                                ['title' => 'Saturday'],
                                ['title' => 'Insurance £500', 'excludes' => 'Insurance £1000,Insurance £2500', 'default' => true],
                                ['title' => 'Insurance £1000', 'excludes' => 'Insurance £500,Insurance £2500'],
                                ['title' => 'Insurance £2500', 'excludes' => 'Insurance £500,Insurance £1000'],
                            ]
                        ],
                        'RMSD9' => [
                            'packageTypes' => ['Parcel'],
                            'addOns' => [
                                ['title' => 'Saturday'],
                                ['title' => 'Insurance £50', 'excludes' => 'Insurance £1000,Insurance £2500', 'default' => true],
                                ['title' => 'Insurance £1000', 'excludes' => 'Insurance £50,Insurance £2500'],
                                ['title' => 'Insurance £2500', 'excludes' => 'Insurance £50,Insurance £1000'],
                            ]
                        ],
                        'STL1'  => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        'STL2'  => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        'OL' => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Printed Papers', 'Parcel'],
                            'addOns' => []
                        ],
                        'OS' => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Printed Papers', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                                ['title' => 'Compensation'],
                            ]
                        ],
                        'OT' => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Printed Papers', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                                ['title' => 'Compensation'],
                            ]
                        ],
                    ]
                ]
            ],
            NetDespatchOrderCreateService::class => [
                'parameters' => [
                    // Don't use our FailoverClient, use Guzzle directly, as this is for talking to a third-party
                    'guzzleClient' => \Guzzle\Http\Client::class,
                ]
            ],
            NetDespatchAccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'netdespatch_cryptor',
                ]
            ],
        ]
    ]
];