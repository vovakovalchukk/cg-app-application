<?php

use CG\NetDespatch\Account\CreationService as NetDespatchAccountCreationService;
use CG\NetDespatch\Order\CreateService as NetDespatchOrderCreateService;
use CG\NetDespatch\ShippingOptionsProvider as NetDespatchShippingOptionsProvider;
use CG\NetDespatch\ShippingService as NetDespatchShippingService;

return [
    'di' => [
        'instance' => [
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
                    ],
                    'shippingServices' => [
                        // These codes are prefixes, more characters will be added based on chosen options
                        '24' => [
                            'name' => '24',
                            'domestic' => true,
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        '48' => [
                            'name' => '48',
                            'domestic' => true,
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        '24F' => [
                            'name' => '24 (Flat)',
                            'domestic' => true,
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        '48F' => [
                            'name' => '48 (Flat)',
                            'domestic' => true,
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        'TPN01' => [
                            'name' => '24 Tracked',
                            'domestic' => true,
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature', 'excludes' => 'Safe Place'],
                                ['title' => 'Safe Place', 'excludes' => 'Signature'],
                            ]
                        ],
                        'TPS01' => [
                            'name' => '48 Tracked',
                            'domestic' => true,
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature', 'excludes' => 'Safe Place'],
                                ['title' => 'Safe Place', 'excludes' => 'Signature'],
                            ]
                        ],
                        'RMSD1' => [
                            'name' => 'Special Delivery 1pm',
                            'domestic' => true,
                            'packageTypes' => ['Parcel'],
                            'addOns' => [
                                ['title' => 'Saturday'],
                                ['title' => 'Insurance £500', 'excludes' => 'Insurance £1000,Insurance £2500', 'default' => true],
                                ['title' => 'Insurance £1000', 'excludes' => 'Insurance £500,Insurance £2500'],
                                ['title' => 'Insurance £2500', 'excludes' => 'Insurance £500,Insurance £1000'],
                            ]
                        ],
                        'RMSD9' => [
                            'name' => 'Special Delivery 9am',
                            'domestic' => true,
                            'packageTypes' => ['Parcel'],
                            'addOns' => [
                                ['title' => 'Saturday'],
                                ['title' => 'Insurance £50', 'excludes' => 'Insurance £1000,Insurance £2500', 'default' => true],
                                ['title' => 'Insurance £1000', 'excludes' => 'Insurance £50,Insurance £2500'],
                                ['title' => 'Insurance £2500', 'excludes' => 'Insurance £50,Insurance £1000'],
                            ]
                        ],
                        'STL1'  => [
                            'name' => '1st Class (STL)',
                            'domestic' => true,
                            'packageTypes' => ['Letter', 'Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        'STL2'  => [
                            'name' => '2nd Class (STL)',
                            'domestic' => true,
                            'packageTypes' => ['Letter', 'Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        'OL' => [
                            'name' => 'International Economy',
                            'domestic' => false,
                            'packageTypes' => ['Letter', 'Large Letter', 'Printed Papers', 'Parcel'],
                            'addOns' => []
                        ],
                        'OS' => [
                            'name' => 'International Standard',
                            'domestic' => false,
                            'packageTypes' => ['Letter', 'Large Letter', 'Printed Papers', 'Parcel'],
                            'addOns' => [
                                [
                                    'title' => 'Signature',
                                    'countries' => [
                                        'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BF',
                                        'BH', 'BI', 'BJ', 'BM', 'BN', 'BO', 'BR', 'BS', 'BT', 'BW', 'BZ', 'CD', 'CF', 'CG',
                                        'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CU', 'CV', 'CX', 'DJ', 'DM', 'DO', 'DZ',
                                        'EE', 'EG', 'EH', 'ER', 'ET', 'FJ', 'FK', 'FO', 'GA', 'GD', 'GE', 'GF', 'GH', 'GL',
                                        'GM', 'GN', 'GP', 'GQ', 'GT', 'GW', 'GY', 'HN', 'HT', 'IL', 'IM', 'IN', 'IO', 'IQ',
                                        'IR', 'JM', 'JO', 'KE', 'KG', 'KI', 'KM', 'KN', 'KP', 'KW', 'KZ', 'LA', 'LB', 'LC',
                                        'LK', 'LR', 'LS', 'LY', 'MA', 'ME', 'MF', 'MG', 'MK', 'ML', 'MM', 'MN', 'MO', 'MQ',
                                        'MR', 'MS', 'MU', 'MV', 'MW', 'MX', 'MZ', 'NA', 'NC', 'NE', 'NG', 'NI', 'NO', 'NP',
                                        'NR', 'NU', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PN', 'PR', 'PW', 'PY', 'QA',
                                        'RE', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SH', 'SL', 'SN', 'SR', 'ST', 'SV', 'SY',
                                        'SZ', 'TC', 'TD', 'TF', 'TG', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TV', 'TW', 'TZ',
                                        'UA', 'UG', 'UY', 'UZ', 'VC', 'VE', 'VG', 'VN', 'VU', 'WF', 'WS', 'YE', 'YT', 'ZA',
                                        'ZM', 'ZW'
                                    ]
                                ],
                                // Standard only allows for Compensation when its with Signature
                                ['title' => 'Compensation', 'requires' => 'Signature'],
                            ],
                        ],
                        'OT' => [
                            'name' => 'International Tracked',
                            'domestic' => false,
                            'packageTypes' => ['Letter', 'Large Letter', 'Printed Papers', 'Parcel'],
                            'addOns' => [
                                [
                                    'title' => 'Signature',
                                    'countries' => [
                                        'AD', 'AE', 'AR', 'AT', 'BE', 'BG', 'BY', 'CA', 'CH', 'CY', 'CZ', 'DE', 'DK', 'EC',
                                        'ES', 'FI', 'FR', 'GI', 'GR', 'HK', 'HR', 'HU', 'ID', 'IE', 'IS', 'IT', 'JP', 'KH',
                                        'KR', 'KY', 'LI', 'LT', 'LU', 'LV', 'MD', 'MT', 'MY', 'NL', 'NZ', 'PL', 'PT', 'RO',
                                        'RS', 'SE', 'SF', 'SG', 'SI', 'SK', 'SM', 'TH', 'TR', 'TT', 'US', 'VA'
                                    ]
                                ],
                                ['title' => 'Compensation'],
                            ],
                            'countries' => [
                                'AD', 'AT', 'AU', 'BE', 'BR', 'CA', 'CH', 'CY', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'HK',
                                'HR', 'HU', 'IE', 'IL', 'IM', 'IN', 'IS', 'IT', 'KR', 'LB', 'LI', 'LT', 'LU', 'LV', 'MT',
                                'MY', 'NL', 'NZ', 'PL', 'PT', 'RU', 'SE', 'SF', 'SG', 'SI', 'SK', 'SM', 'TR', 'US', 'VA'
                            ]
                        ],
                        'MP' => [
                            'name' => 'International Business',
                            'domestic' => false,
                            'packageTypes' => ['Letter', 'Large Letter', 'Parcel'],
                            'addOns' => [
                                [
                                    'title' => 'Signature',
                                    'default' => true,
                                    'excludes' => 'Priority',
                                    'countries' => [
                                        'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BF',
                                        'BH', 'BI', 'BJ', 'BM', 'BN', 'BO', 'BR', 'BS', 'BT', 'BW', 'BZ', 'CD', 'CF', 'CG',
                                        'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CU', 'CV', 'CX', 'DJ', 'DM', 'DO', 'DZ',
                                        'EE', 'EG', 'EH', 'ER', 'ET', 'FJ', 'FK', 'FO', 'GA', 'GD', 'GE', 'GF', 'GH', 'GL',
                                        'GM', 'GN', 'GP', 'GQ', 'GT', 'GW', 'GY', 'HN', 'HT', 'IL', 'IM', 'IN', 'IO', 'IQ',
                                        'IR', 'JM', 'JO', 'KE', 'KG', 'KI', 'KM', 'KN', 'KP', 'KW', 'KZ', 'LA', 'LB', 'LC',
                                        'LK', 'LR', 'LS', 'LY', 'MA', 'ME', 'MF', 'MG', 'MK', 'ML', 'MM', 'MN', 'MO', 'MQ',
                                        'MR', 'MS', 'MU', 'MV', 'MW', 'MX', 'MZ', 'NA', 'NC', 'NE', 'NG', 'NI', 'NO', 'NP',
                                        'NR', 'NU', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PN', 'PR', 'PW', 'PY', 'QA',
                                        'RE', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SH', 'SL', 'SN', 'SR', 'ST', 'SV', 'SY',
                                        'SZ', 'TC', 'TD', 'TF', 'TG', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TV', 'TW', 'TZ',
                                        'UA', 'UG', 'UY', 'UZ', 'VC', 'VE', 'VG', 'VN', 'VU', 'WF', 'WS', 'YE', 'YT', 'ZA',
                                        'ZM', 'ZW'
                                    ]
                                ],
                                // Standard only allows for Compensation when its with Signature
                                ['title' => 'Compensation', 'requires' => 'Signature', 'excludes' => 'Priority'],
                                ['title' => 'Priority', 'default' => true, 'excludes' => 'Signature,Compensation'],
                                ['title' => 'Max Sort', 'requires' => 'Priority'],
                            ],
                        ],
                        'MT' => [
                            'name' => 'International Business Tracked',
                            'domestic' => false,
                            'packageTypes' => ['Letter', 'Large Letter', 'Parcel'],
                            'addOns' => [
                                [
                                    'title' => 'Signature',
                                    'countries' => [
                                        'AD', 'AE', 'AR', 'AT', 'BE', 'BG', 'BY', 'CA', 'CH', 'CY', 'CZ', 'DE', 'DK', 'EC',
                                        'ES', 'FI', 'FR', 'GI', 'GR', 'HK', 'HR', 'HU', 'ID', 'IE', 'IS', 'IT', 'JP', 'KH',
                                        'KR', 'KY', 'LI', 'LT', 'LU', 'LV', 'MD', 'MT', 'MY', 'NL', 'NZ', 'PL', 'PT', 'RO',
                                        'RS', 'SE', 'SF', 'SG', 'SI', 'SK', 'SM', 'TH', 'TR', 'TT', 'US', 'VA'
                                    ]
                                ],
                                ['title' => 'Compensation'],
                            ],
                            'countries' => [
                                'AD', 'AT', 'AU', 'BE', 'BR', 'CA', 'CH', 'CY', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'HK',
                                'HR', 'HU', 'IE', 'IL', 'IM', 'IN', 'IS', 'IT', 'KR', 'LB', 'LI', 'LT', 'LU', 'LV', 'MT',
                                'MY', 'NL', 'NZ', 'PL', 'PT', 'RU', 'SE', 'SF', 'SG', 'SI', 'SK', 'SM', 'TR', 'US', 'VA'
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
