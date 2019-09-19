<?php

use CG\NetDespatch\Account\CreationService as NetDespatchAccountCreationService;
use CG_NetDespatch\Account\CreationService\RoyalMail as NDRMAccountCreationService;
use CG\NetDespatch\Carrier\CarrierSpecificData\UkMail\PackageType as UKMailPackageType;
use CG\NetDespatch\Carrier\CarrierSpecificData\UkMail\Signature as UKMailSignature;
use CG_NetDespatch\Controller\AccountController as NetDespatchAccountController;
use CG\NetDespatch\Order\CreateService as NetDespatchOrderCreateService;
use CG\NetDespatch\Order\Mapper\Factory as NetDespatchOrderMapperFactory;
use CG\NetDespatch\Order\Mapper\UkMail\TariffCode as UKMailTariffCodeMapper;
use CG\NetDespatch\ShippingOptionsProvider as NetDespatchShippingOptionsProvider;

$ukMailBagitAndParcel = [
    UKMailPackageType::BAGIT_SMALL,
    UKMailPackageType::BAGIT_MEDIUM,
    UKMailPackageType::BAGIT_LARGE,
    UKMailPackageType::BAGIT_XL,
    UKMailPackageType::PARCEL
];
$ukMailAllSigAddOns = [
    [
        'title' => UKMailSignature::ADDRESS_OR_NEIGHBOUR,
        'excludes' => implode(',', [UKMailSignature::ADDRESS_ONLY, UKMailSignature::LEAVE_SAFE]),
        'default' => true
    ],
    [
        'title' => UKMailSignature::ADDRESS_ONLY,
        'excludes' => implode(',', [UKMailSignature::ADDRESS_OR_NEIGHBOUR, UKMailSignature::LEAVE_SAFE])
    ],
    [
        'title' => UKMailSignature::LEAVE_SAFE,
        'excludes' => implode(',', [UKMailSignature::ADDRESS_OR_NEIGHBOUR, UKMailSignature::ADDRESS_ONLY])
    ],
];

return [
    'di' => [
        'instance' => [
            NetDespatchShippingOptionsProvider::class => [
                'parameters' => [
                    'defaultBookingOptions' => [
                        'weight' => 'weight',
                        'height' => 'height',
                        'width'  => 'width',
                        'length' => 'length',
                        'deliveryInstructions' => 'deliveryInstructions',
                    ],
                    'carriersConfig' => [
                        'royal-mail' => [
                            'channelName' => 'royal-mail-nd',
                            'displayName' => 'Royal Mail (OBA)',
                            'providerCode' => 'ROYALMAIL',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            // For Royal Mail we use a bespoke form. See: CG_NetDespatch\Setup\RoyalMail
                            'fields' => [],
                            'bookingOptions' => [
                                'weight' => 'weight',
                                'packageType' => 'packageType',
                                'addOns' => 'addOns',
                                'deliveryInstructions' => 'deliveryInstructions',
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
                        ],

                        'uk-mail' => [
                            'channelName' => 'uk-mail-nd',
                            'displayName' => 'UK Mail',
                            'providerCode' => 'Amtrak',
                            'featureFlag' => 'UK Mail',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'fields' => [],
                            'bookingOptions' => [
                                'parcels' => 'parcels',
                                'weight' => 'weight',
                                'height' => 'height',
                                'width'  => 'width',
                                'length' => 'length',
                                'packageType' => 'packageType',
                                'addOns' => 'addOns',
                                'deliveryInstructions' => 'deliveryInstructions',
                            ],
                            'shippingServices' => [
                                // These aren't the final codes, see UKMailTariffCodeMapper
                                'Next Day' => [
                                    'name' => 'Next Day',
                                    'domestic' => true,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => $ukMailAllSigAddOns
                                ],
                                'Next Day 12:00' => [
                                    'name' => 'Next Day 12:00',
                                    'domestic' => true,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => $ukMailAllSigAddOns
                                ],
                                'Next Day 10:30' => [
                                    'name' => 'Next Day 10:30',
                                    'domestic' => true,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => $ukMailAllSigAddOns
                                ],
                                'Next Day 09:00' => [
                                    'name' => 'Next Day 09:00',
                                    'domestic' => true,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::ADDRESS_ONLY,
                                            'default' => true
                                        ]
                                    ]
                                ],
                                'Saturday' => [
                                    'name' => 'Saturday',
                                    'domestic' => true,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => $ukMailAllSigAddOns
                                ],
                                'Saturday 10:30' => [
                                    'name' => 'Saturday 10:30',
                                    'domestic' => true,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => $ukMailAllSigAddOns
                                ],
                                'Saturday 09:00' => [
                                    'name' => 'Saturday 09:00',
                                    'domestic' => true,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::ADDRESS_ONLY,
                                            'default' => true
                                        ]
                                    ]
                                ],
                                // Deliberately omitting 'Timed Delivery'
                                '48 Hr' => [
                                    'name' => '48 Hr',
                                    'domestic' => true,
                                    'packageTypes' => [UKMailPackageType::PARCEL],
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::ADDRESS_OR_NEIGHBOUR,
                                            'excludes' => implode(',', [UKMailSignature::ADDRESS_ONLY, UKMailSignature::LEAVE_SAFE]),
                                            'default' => true
                                        ],
                                        [
                                            'title' => UKMailSignature::LEAVE_SAFE,
                                            'excludes' => implode(',', [UKMailSignature::ADDRESS_OR_NEIGHBOUR, UKMailSignature::ADDRESS_ONLY])
                                        ],
                                    ]
                                ],
                                '48 Hr +' => [
                                    'name' => '48 Hr +',
                                    'domestic' => true,
                                    'packageTypes' => [UKMailPackageType::PARCEL],
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::LEAVE_SAFE,
                                            'default' => true
                                        ],
                                    ]
                                ],
                                'Packet+' => [
                                    'name' => 'Packet+',
                                    'domestic' => true,
                                    'packageTypes' => [UKMailPackageType::PACKET],
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::LEAVE_SAFE,
                                            'default' => true
                                        ],
                                    ]
                                ],
                                'Pallet 24hrs' => [
                                    'name' => 'Pallet 24hrs',
                                    'domestic' => true,
                                    'packageTypes' => [UKMailPackageType::PALLET],
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::ADDRESS_ONLY,
                                            'default' => true
                                        ],
                                    ]
                                ],
                                'Pallet 48hrs' => [
                                    'name' => 'Pallet 48hrs',
                                    'domestic' => true,
                                    'packageTypes' => [UKMailPackageType::PALLET],
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::ADDRESS_ONLY,
                                            'default' => true
                                        ],
                                    ]
                                ],
                                'International: Air' => [
                                    'name' => 'International: Air',
                                    'domestic' => false,
                                    'packageTypes' => [UKMailPackageType::PARCEL],
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::ADDRESS_ONLY,
                                            'default' => true
                                        ],
                                    ]
                                ],
                                'International: Road' => [
                                    'name' => 'International: Road',
                                    'domestic' => false,
                                    'packageTypes' => [UKMailPackageType::PARCEL],
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::ADDRESS_ONLY,
                                            'default' => true
                                        ],
                                    ]
                                ],
                            ]
                        ],
                        'yodel' => [
                            'channelName' => 'yodel-nd',
                            'displayName' => 'Yodel',
                            'providerCode' => '11',
                            'featureFlag' => 'Yodel',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'fields' => [],
                            'bookingOptions' => [
                                'weight' => 'weight',
                                'height' => 'height',
                                'width'  => 'width',
                                'length' => 'length',
                            ],
                            'shippingServices' => [
                                'DIA' => [
                                    'name' => 'Priority 10:00',
                                    'domestic' => true,
                                ],
                                'NOON' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                'SAT' => [
                                    'name' => 'Saturday Priority 12:00',
                                    'domestic' => true,
                                ],
                                '1EXP' => [
                                    'name' => 'UK Expect 24 POD',
                                    'domestic' => true,
                                ],
                                'ISLE' => [ //will replace STD for B2B or HDN for B2C
                                    'name' => 'UK Express (Isle)',
                                    'domestic' => true,
                                ],
                                'STD' => [
                                    'name' => 'UK Express 24',
                                    'domestic' => true,
                                ],
                                '1EB' => [
                                    'name' => 'UK Express 24 (NI INT)',
                                    'domestic' => true,
                                ],
                                '1EU' => [
                                    'name' => 'UK Express 24 (UK)',
                                    'domestic' => true,
                                ],
                                'ECO' => [
                                    'name' => 'UK Express 48',
                                    'domestic' => true,
                                ],
                                'NIS' => [
                                    'name' => 'UK Express 48 (NI)',
                                    'domestic' => true,
                                ],
                                'STSA' => [
                                    'name' => 'UK Express Saturday',
                                    'domestic' => true,
                                ],
                                'HDN' => [
                                    'name' => 'UK Home Delivery 24',
                                    'domestic' => true,
                                ],
                                '1HB' => [
                                    'name' => 'UK Home Delivery 24 (BT)',
                                    'domestic' => true,
                                ],
                                'HECO' => [
                                    'name' => 'UK Home Delivery 48',
                                    'domestic' => true,
                                ],
                                '2HB' => [
                                    'name' => 'UK Home Delivery 48 (BT)',
                                    'domestic' => true,
                                ],
                                '2HN' => [
                                    'name' => 'UK Home Delivery 48 (NI)',
                                    'domestic' => true,
                                ],
                                'HDPA' => [
                                    'name' => 'UK Home Delivery 72',
                                    'domestic' => true,
                                ],
                                'HIPA' => [
                                    'name' => 'UK Home Delivery 72 (NI)',
                                    'domestic' => true,
                                ],
                                '5HCT' => [
                                    'name' => 'UK Home Delivery Catalogue',
                                    'domestic' => true,
                                ],
                                'HSAT' => [
                                    'name' => 'UK Home Delivery Saturday',
                                    'domestic' => true,
                                ],
                                'PACK' => [
                                    'name' => 'UK Packet Service',
                                    'domestic' => true,
                                ],
                                '1CP' => [
                                    'name' => 'Xpress 24 POD',
                                    'domestic' => true,
                                ],
                                '1CN' => [
                                    'name' => 'Xpress 24 NON POD',
                                    'domestic' => true,
                                ],
                                '2CP' => [
                                    'name' => 'Xpress 48 POD',
                                    'domestic' => true,
                                ],
                                '2CN' => [
                                    'name' => 'Xpress 48 NON POD',
                                    'domestic' => true,
                                ],
                                '1VP' => [
                                    'name' => 'Xpect 24 POD',
                                    'domestic' => true,
                                ],
                                '1VN' => [
                                    'name' => 'Xpect 24 NON POD',
                                    'domestic' => true,
                                ],
                                '2VP' => [
                                    'name' => 'Xpect 48 POD',
                                    'domestic' => true,
                                ],
                                '2VN' => [
                                    'name' => 'Xpect 48 NON POD',
                                    'domestic' => true,
                                ],
                                '2VLP' => [
                                    'name' => 'Xpect 48 XL POD',
                                    'domestic' => true,
                                ],
                                '2VLN' => [
                                    'name' => 'Xpect 48 XL NON POD',
                                    'domestic' => true,
                                ],
                                '1VSP' => [
                                    'name' => 'Xpect Saturday POD',
                                    'domestic' => true,
                                ],
                                '1VSN' => [
                                    'name' => 'Xpect Saturday NON POD',
                                    'domestic' => true,
                                ],
                                '12P' => [
                                    'name' => 'Xpect Pre 12 POD',
                                    'domestic' => true,
                                ],
                                '1VD' => [
                                    'name' => 'Xpert 24 POD Desk',
                                    'domestic' => true,
                                ],
                                '1VA' => [
                                    'name' => 'Xpert 24 Address Only',
                                    'domestic' => true,
                                ],
                                '1VT' => [
                                    'name' => 'Xpert 24 HVT POD',
                                    'domestic' => true,
                                ],
                                '1BFP' => [
                                    'name' => 'Xpert 24 BFPO POD',
                                    'domestic' => true,
                                ],
                                '1VSA' => [
                                    'name' => 'Xpert Saturday Address Only',
                                    'domestic' => true,
                                ],
                                '1VST' => [
                                    'name' => 'Xpert Saturday HVT POD',
                                    'domestic' => true,
                                ],
                                '12N' => [
                                    'name' => 'Xpert Pre 12 NON POD',
                                    'domestic' => true,
                                ],
                                '12T' => [
                                    'name' => 'Xpert Pre 12 HVT Address Only',
                                    'domestic' => true,
                                ],
                                '12A' => [
                                    'name' => 'Xpert Pre 12 Address Only',
                                    'domestic' => true,
                                ],
                                '12SP' => [
                                    'name' => 'Xpert Pre 12 Saturday POD',
                                    'domestic' => true,
                                ],
                                '12SN' => [
                                    'name' => 'Xpert Pre 12 Saturday NON POD',
                                    'domestic' => true,
                                ],
                                '12SA' => [
                                    'name' => 'Xpert Pre 12 Saturday Address Only',
                                    'domestic' => true,
                                ],
                                '12ST' => [
                                    'name' => 'Xpert Pre 12 Saturday HVT Address Only',
                                    'domestic' => true,
                                ],
                                '5CN' => [
                                    'name' => 'Xpert Catalogue NON POD',
                                    'domestic' => true,
                                ],

                                //ROI and CI only
                                '48POD' => [
                                    'name' => 'Yodel 48 POD',
                                    'domestic' => false,
                                    'countries' => [
                                        'GG', 'IE', 'JE'
                                    ]
                                ],
                                '48NPOD' => [
                                    'name' => 'Yodel 48 NON POD',
                                    'domestic' => false,
                                    'countries' => [
                                        'GG', 'IE', 'JE'
                                    ]
                                ],
                                '72POD' => [
                                    'name' => 'Yodel 72 POD',
                                    'domestic' => false,
                                    'countries' => [
                                        'GG', 'IE', 'JE'
                                    ]
                                ],
                                '72NPOD' => [
                                    'name' => 'Yodel 72 NON POD',
                                    'domestic' => false,
                                    'countries' => [
                                        'GG', 'IE', 'JE'
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            UKMailTariffCodeMapper::class => [
                'parameters' => [
                    'map' => [
                        'Next Day' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '1',
                                UKMailSignature::ADDRESS_ONLY         => '220',
                                UKMailSignature::LEAVE_SAFE           => '210',
                            ],
                            UKMailPackageType::BAGIT_SMALL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '40',
                                UKMailSignature::ADDRESS_ONLY         => '240',
                                UKMailSignature::LEAVE_SAFE           => '230',
                            ],
                            UKMailPackageType::BAGIT_MEDIUM => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '30',
                                UKMailSignature::ADDRESS_ONLY         => '250',
                            ],
                            UKMailPackageType::BAGIT_LARGE => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '20',
                                UKMailSignature::ADDRESS_ONLY         => '260',
                            ],
                            UKMailPackageType::BAGIT_XL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '10',
                                UKMailSignature::ADDRESS_ONLY         => '270',
                            ],
                        ],
                        'Next Day 12:00' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '2',
                                UKMailSignature::ADDRESS_ONLY         => '221',
                                UKMailSignature::LEAVE_SAFE           => '211',
                            ],
                            UKMailPackageType::BAGIT_SMALL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '41',
                                UKMailSignature::ADDRESS_ONLY         => '241',
                                UKMailSignature::LEAVE_SAFE           => '231',
                            ],
                            UKMailPackageType::BAGIT_MEDIUM => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '31',
                                UKMailSignature::ADDRESS_ONLY         => '251',
                            ],
                            UKMailPackageType::BAGIT_LARGE => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '21',
                                UKMailSignature::ADDRESS_ONLY         => '261',
                            ],
                            UKMailPackageType::BAGIT_XL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '11',
                                UKMailSignature::ADDRESS_ONLY         => '271',
                            ],
                        ],
                        'Next Day 10:30' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '9',
                                UKMailSignature::ADDRESS_ONLY         => '222',
                                UKMailSignature::LEAVE_SAFE           => '212',
                            ],
                            UKMailPackageType::BAGIT_SMALL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '49',
                                UKMailSignature::ADDRESS_ONLY         => '242',
                                UKMailSignature::LEAVE_SAFE           => '232',
                            ],
                            UKMailPackageType::BAGIT_MEDIUM => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '39',
                                UKMailSignature::ADDRESS_ONLY         => '252',
                            ],
                            UKMailPackageType::BAGIT_LARGE => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '29',
                                UKMailSignature::ADDRESS_ONLY         => '262',
                            ],
                            UKMailPackageType::BAGIT_XL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '19',
                                UKMailSignature::ADDRESS_ONLY         => '272',
                            ],
                        ],
                        'Next Day 09:00' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_ONLY         => '3',
                            ],
                            UKMailPackageType::BAGIT_SMALL => [
                                UKMailSignature::ADDRESS_ONLY         => '42',
                            ],
                            UKMailPackageType::BAGIT_MEDIUM => [
                                UKMailSignature::ADDRESS_ONLY         => '32',
                            ],
                            UKMailPackageType::BAGIT_LARGE => [
                                UKMailSignature::ADDRESS_ONLY         => '22',
                            ],
                            UKMailPackageType::BAGIT_XL => [
                                UKMailSignature::ADDRESS_ONLY         => '12',
                            ],
                        ],
                        'Saturday' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '4',
                                UKMailSignature::ADDRESS_ONLY         => '225',
                                UKMailSignature::LEAVE_SAFE           => '215',
                            ],
                            UKMailPackageType::BAGIT_SMALL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '43',
                                UKMailSignature::ADDRESS_ONLY         => '245',
                                UKMailSignature::LEAVE_SAFE           => '235',
                            ],
                            UKMailPackageType::BAGIT_MEDIUM => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '33',
                                UKMailSignature::ADDRESS_ONLY         => '255',
                            ],
                            UKMailPackageType::BAGIT_LARGE => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '23',
                                UKMailSignature::ADDRESS_ONLY         => '265',
                            ],
                            UKMailPackageType::BAGIT_XL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '13',
                                UKMailSignature::ADDRESS_ONLY         => '275',
                            ],
                        ],
                        'Saturday 10:30' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '7',
                                UKMailSignature::ADDRESS_ONLY         => '226',
                                UKMailSignature::LEAVE_SAFE           => '216',
                            ],
                            UKMailPackageType::BAGIT_SMALL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '46',
                                UKMailSignature::ADDRESS_ONLY         => '246',
                                UKMailSignature::LEAVE_SAFE           => '236',
                            ],
                            UKMailPackageType::BAGIT_MEDIUM => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '36',
                                UKMailSignature::ADDRESS_ONLY         => '256',
                            ],
                            UKMailPackageType::BAGIT_LARGE => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '26',
                                UKMailSignature::ADDRESS_ONLY         => '266',
                            ],
                            UKMailPackageType::BAGIT_XL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '16',
                                UKMailSignature::ADDRESS_ONLY         => '276',
                            ],
                        ],
                        'Saturday 09:00' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_ONLY         => '5',
                            ],
                            UKMailPackageType::BAGIT_SMALL => [
                                UKMailSignature::ADDRESS_ONLY         => '44',
                            ],
                            UKMailPackageType::BAGIT_MEDIUM => [
                                UKMailSignature::ADDRESS_ONLY         => '34',
                            ],
                            UKMailPackageType::BAGIT_LARGE => [
                                UKMailSignature::ADDRESS_ONLY         => '24',
                            ],
                            UKMailPackageType::BAGIT_XL => [
                                UKMailSignature::ADDRESS_ONLY         => '14',
                            ],
                        ],
                        '48 Hr' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_OR_NEIGHBOUR => '48',
                                // This has the same code as the 48 Hr + (72) service below
                                UKMailSignature::LEAVE_SAFE           => '72',
                            ],
                        ],
                        '48 Hr +' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::LEAVE_SAFE           => '72',
                            ],
                        ],
                        'Packet+' => [
                            UKMailPackageType::PACKET => [
                                UKMailSignature::LEAVE_SAFE           => '545',
                            ]
                        ],
                        'Pallet 24hrs' => [
                            UKMailPackageType::PALLET => [
                                UKMailSignature::ADDRESS_ONLY         => '97',
                            ]
                        ],
                        'Pallet 48hrs' => [
                            UKMailPackageType::PALLET => [
                                UKMailSignature::ADDRESS_ONLY         => '98',
                            ]
                        ],
                        'International: Air' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_ONLY         => '101',
                            ]
                        ],
                        'International: Road' => [
                            UKMailPackageType::PARCEL => [
                                UKMailSignature::ADDRESS_ONLY         => '204',
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
            NDRMAccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'netdespatch_cryptor',
                ]
            ],
            NetDespatchOrderMapperFactory::class => [
                'parameters' => [
                    'cryptor' => 'netdespatch_cryptor',
                ]
            ],
            NetDespatchAccountController::class => [
                'parameters' => [
                    'cryptor' => 'netdespatch_cryptor',
                ]
            ]
        ]
    ]
];
