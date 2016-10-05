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
                        // These codes are the complete codes
                        'DE1E' => 'Bus Pcls Zero HV RDC Prty',
                        'DE3E' => 'Bus Pcls Zero HV RDC Econ',
                        'DE4E' => 'Bus Pcls Zero LV Prty',
                        'DE6E' => 'Bus Pcls Zero LV Econ',
                        'DG1G' => 'Bus Mail LL Ctry HV RDC Prty',
                        'DG3G' => 'Bus Mail LL Ctry HV RDC Econ',
                        'DG4G' => 'Bus Mail LL Ctry LV Prty',
                        'DG6G' => 'Bus Mail LL Ctry LV Econ',
                        'DW1E' => 'Bus Pcls Bespoke',
                        'IE1E' => 'Bus Pcls Zone Prty',
                        'IE3E' => 'Bus Pcls Zone Econ',
                        'IG1G' => 'Bus Mail LL Zone Prty',
                        'IG3G' => 'Bus Mail LL Zone Econ',
                        'IG4G' => 'Bus Mail LL Zone Prty Mch',
                        'IG6G' => 'Bus Mail LL Zone Econ Mch',
                        'MB1N' => 'Bus Pcls Print Direct Prty',
                        'MB2N' => 'Bus Pcls Print Direct Std',
                        'MB3N' => 'Bus Pcls Print Direct Econ',
                        'MP0E' => 'Bus Pcls Signed +Comp Ctry',
                        'MP1E' => 'Bus Pcls Tracked',
                        'MP4E' => 'Bus Pcls Tracked +Comp',
                        'MP5E' => 'Bus Pcls Signed',
                        'MP6E' => 'Bus Pcls Signed +Comp',
                        'MP7E' => 'Bus Pcls Tracked Ctry',
                        'MP8E' => 'Bus Pcls Tracked +Comp Ctry',
                        'MP9E' => 'Bus Pcls Signed Ctry',
                        'MTAE' => 'Bus Pcls TandS',
                        'MTBE' => 'Bus Pcls TandS +Comp',
                        'MTCG' => 'Bus Mail TandS LL',
                        'MTCP' => 'Bus Mail TandS Letter',
                        'MTDG' => 'Bus Mail TandS +Comp LL',
                        'MTDP' => 'Bus Mail TandS +Comp Letter',
                        'MTEE' => 'Bus Pcls TandS Ctry',
                        'MTFE' => 'Bus Pcls TandS +Comp Ctry',
                        'MTGG' => 'Bus Mail TandS Ctry LL',
                        'MTHG' => 'Bus Mail TandS +Comp Ctry LL',
                        'MTIG' => 'Bus Mail Tracked LL',
                        'MTIP' => 'Bus Mail Tracked Letter',
                        'MTJG' => 'Bus Mail Tracked +Comp LL',
                        'MTJP' => 'Bus Mail Tracked +Comp Letter',
                        'MTKG' => 'Bus Mail Tracked Ctry LL',
                        'MTLG' => 'Bus Mail Tracked +Comp Ctry LL',
                        'MTMG' => 'Bus Mail Signed LL',
                        'MTMP' => 'Bus Mail Signed Letter',
                        'MTNG' => 'Bus Mail Signed +Comp LL',
                        'MTNP' => 'Bus Mail Signed +Comp Letter',
                        'MTOG' => 'Bus Mail Signed Ctry LL',
                        'MTPG' => 'Bus Mail Signed +Comp Ctry LL',
                        'MTQE' => 'Bus Pcls Zone Plus Prty',
                        'MTSE' => 'Bus Pcls Zone Plus Econ',
                        'NDE1E' => 'Test Copy of DE1E Tariff',
                        'OLAE' => 'Std on Account Pcls',
                        'OLAG' => 'Std on Acct LL',
                        'OLAH' => 'Std on Account PrtPaper',
                        'OLAP' => 'Std on Account Letter',
                        'OLSE' => 'Econ on Account Pcls',
                        'OLSG' => 'Econ on Acct LL',
                        'OLSH' => 'Econ on Acct PrtPaper',
                        'OLSP' => 'Econ on Account Letter',
                        'OSAE' => 'Signed on Acct Pcls',
                        'OSAG' => 'Signed on Acct LL',
                        'OSAH' => 'Signed on Acct PrtPaper',
                        'OSAP' => 'Signed on Acct Letter',
                        'OSBE' => 'Signed on Acct +Comp Pcls',
                        'OSBG' => 'Signed on Acct +Comp LL',
                        'OSBH' => 'Signed on Acct +Comp PrtPaper',
                        'OSBP' => 'Signed on Acct +Comp Letter',
                        'OTAE' => 'Tracked on Acct Pcls',
                        'OTAG' => 'Tracked on Acct LL',
                        'OTAH' => 'Tracked on Acct PrtPaper',
                        'OTAP' => 'Tracked on Acct Letter',
                        'OTBE' => 'Tracked on Acct +Comp Pcls',
                        'OTBG' => 'Tracked on Acct +Comp LL',
                        'OTBH' => 'Tracked on Acct +Comp PrtPaper',
                        'OTBP' => 'Tracked on Acct +Comp Letter',
                        'OTCE' => 'TandS on Acct Pcls',
                        'OTCG' => 'TandS on Acct LL',
                        'OTCH' => 'TandS on Acct PrtPaper',
                        'OTCP' => 'TandS on Acct Letter',
                        'OTDE' => 'TandS on Acct +Comp Pcls',
                        'OTDG' => 'TandS on Acct +Comp LL',
                        'OTDH' => 'TandS on Acct +Comp PrtPaper',
                        'OTDP' => 'TandS on Acct +Comp Letter',
                        'OZ1N' => 'Bus Mail Mixd Prty',
                        'OZ3N' => 'Bus Mail Mixd Econ',
                        'OZ4N' => 'Bus Mail Mixd Prty Mch',
                        'OZ6N' => 'Bus Mail Mixd Econ Mch',
                        'PS0E' => 'Bus Pcls Max Econ',
                        'PS7G' => 'Bus Mail LL Max Prty',
                        'PS8G' => 'Bus Mail LL Max Econ',
                        'PS9E' => 'Bus Pcls Max Prty',
                        'PSBG' => 'Bus Mail LL Max Std',
                        'PSCE' => 'Bus Pcls Max Std',
                        'WE1E' => 'Bus Pcls Zero Prty',
                        'WE3E' => 'Bus Pcls Zero Econ',
                        'WG1G' => 'Bus Mail LL Zero Prty',
                        'WG3G' => 'Bus Mail LL Zero Econ',
                        'WG4G' => 'Bus Mail LL Zero Prty Mch',
                        'WG6G' => 'Bus Mail LL Zero Econ Mch',
                        'WW1N' => 'Bus Mail Mixd Zero Prty',
                        'WW3N' => 'Bus Mail Mixd Zero Econ',
                        'WW4N' => 'Bus Mail Mixd Zero Prty Mch',
                        'WW6N' => 'Bus Mail Mixd Zero Econ Mch',
                    ]
                ]
            ],
            NetDespatchShippingOptionsProvider::class => [
                'parameters' => [
                    'carrierBookingOptionsDomestic' => [
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
                    'carrierBookingOptionsInternational' => [
                        'parcels' => false,
                        'collectionDate' => false,
                        'weight' => true,
                        'packageType' => false,
                        'addOns' => false,
                        'height' => true,
                        'width' => true,
                        'length' => true,
                        'tradeTariffCode' => true,
                        'insurance' => false,
                        'insuranceMonetary' => false,
                        'signature' => false,
                        'deliveryInstructions' => true,
                        'itemParcelAssignment' => true,
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