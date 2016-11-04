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
                        'OL' => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Printed Papers', 'Parcel'],
                            'addOns' => []
                        ],
                        'OS' => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Printed Papers', 'Parcel'],
                            'addOns' => [
                                [
                                    'title' => 'Signature',
                                    'countries' => [
                                        'AF', 'AX', 'AL', 'DZ', 'AD', 'AO', 'AI', 'AG', 'AR', 'AM', 'AW', 'AC', 'AU', 'AT',
                                        'AZ', 'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BQ', 'BA',
                                        'BW', 'BR', 'IO', 'BN', 'BF', 'BI', 'CM', 'CV', 'CF', 'EA', 'TD', 'CL', 'CN', 'CX',
                                        'CO', 'KM', 'CG', 'CD', 'CK', 'CR', 'CU', 'CW', 'DJ', 'DM', 'DO', 'EG', 'SV', 'GQ',
                                        'ER', 'EE', 'ET', 'FK', 'FO', 'FJ', 'GF', 'PF', 'TF', 'GA', 'GM', 'GE', 'GH', 'GL',
                                        'GD', 'GP', 'GT', 'GN', 'GW', 'GY', 'HT', 'HN', 'IN', 'IR', 'IQ', 'IM', 'IT', 'JM',
                                        'JO', 'KZ', 'KE', 'KI', 'KO', 'KW', 'KG', 'LA', 'LB', 'LS', 'LR', 'LY', 'MO', 'MK',
                                        'MG', 'MW', 'MV', 'ML', 'MQ', 'MR', 'MU', 'MX', 'MN', 'ME', 'MS', 'MA', 'MZ', 'MM',
                                        'NA', 'NR', 'NP', 'NC', 'NI', 'NE', 'NG', 'NU', 'KP', 'NO', 'OM', 'PK', 'PW', 'PA',
                                        'PG', 'PY', 'PE', 'PH', 'PN', 'PR', 'QA', 'RE', 'RU', 'RW', 'ST', 'SA', 'SN', 'SC',
                                        'SL', 'SB', 'ZA', 'SS', 'LK', 'XE', 'SH', 'SD', 'SR', 'SZ', 'SY', 'TW', 'TJ', 'TZ',
                                        'TL', 'TG', 'TK', 'TO', 'TA', 'TN', 'TM', 'TC', 'TV', 'UG', 'UA', 'UY', 'UZ', 'VU',
                                        'VE', 'VN', 'WF', 'EH', 'YE', 'ZM', 'ZW'
                                    ]
                                ],
                                // Standard only allows for Compensation when its with Signature
                                ['title' => 'Compensation', 'requires' => 'Signature'],
                            ],
                        ],
                        'OT' => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Printed Papers', 'Parcel'],
                            'addOns' => [
                                [
                                    'title' => 'Signature',
                                    'countries' => [
                                        'AD', 'AR', 'AT', 'BY', 'BE', 'BG', 'KH', 'CA', 'IC', 'KY', 'HR', 'CY', 'CZ', 'DK',
                                        'EC', 'FI', 'FR', 'DE', 'GI', 'GR', 'HK', 'HU', 'IS', 'ID', 'IE', 'IL', 'JP', 'LV',
                                        'LI', 'LT', 'LU', 'MY', 'MT', 'MD', 'NL', 'NZ', 'PL', 'PT', 'RO', 'SM', 'RS', 'SG',
                                        'SK', 'SI', 'KR', 'ES', 'SE', 'CH', 'TH', 'TT', 'TR', 'AE', 'US', 'VA'
                                    ]
                                ],
                                ['title' => 'Compensation'],
                            ],
                            'countries' => [
                                'AD', 'AU', 'AT', 'BE', 'BR', 'CA', 'IC', 'HR', 'DK', 'EE', 'FI', 'FR', 'DE', 'HK', 'HU',
                                'IS', 'IN', 'IE', 'IM', 'IL', 'LV', 'LI', 'LT', 'LU', 'MY', 'MT', 'NL', 'NZ', 'PL', 'PT',
                                'SM', 'SG', 'SK', 'SI', 'KR', 'ES', 'SE', 'CH', 'TR', 'US', 'VA'
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