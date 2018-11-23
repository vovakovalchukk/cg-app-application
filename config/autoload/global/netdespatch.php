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
                            'providerCode' => 'Amtrak',
                            'featureFlag' => 'Yodel',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'fields' => [],
                            'bookingOptions' => [
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

                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '20' => [
                                    'name' => 'Express 24',
                                    'domestic' => true,
                                ],
                                '30' => [
                                    'name' => 'Express 48',
                                    'domestic' => true,
                                ],
                                '40' => [
                                    'name' => 'Saturday Priority 12:00',
                                    'domestic' => true,
                                ],
                                '60' => [
                                    'name' => 'Express Isle',
                                    'domestic' => true,
                                ],
                                '70' => [
                                    'name' => 'Express 24 Return',
                                    'domestic' => true,
                                ],
                                '100' => [
                                    'name' => 'Express 24 Pallet',
                                    'domestic' => true,
                                ],
                                '120' => [
                                    'name' => 'Express 24 (BT)',
                                    'domestic' => true,
                                ],
                                '140' => [
                                    'name' => 'Express 48 (UK)',
                                    'domestic' => true,
                                ],
                                '150' => [
                                    'name' => 'Yodel World',
                                    'domestic' => false,
                                ],
                                '151' => [
                                    'name' => 'Yodel ROI 48 POD',
                                    'domestic' => false,
                                ],
                                '153' => [
                                    'name' => 'Yodel ROI 48 NON POD',
                                    'domestic' => false,
                                ],
                                '154' => [
                                    'name' => 'Yodel ROI 72 POD',
                                    'domestic' => false,
                                ],
                                '151' => [
                                    'name' => 'Yodel ROI 72 NON POD',
                                    'domestic' => false,
                                ],
                                '155' => [
                                    'name' => 'Yodel CI 48 POD',
                                    'domestic' => false,
                                ],
                                '156' => [
                                    'name' => 'Yodel CI 48 NON POD',
                                    'domestic' => false,
                                ],
                                '157' => [
                                    'name' => 'Yodel CI 72 POD',
                                    'domestic' => false,
                                ],
                                '158' => [
                                    'name' => 'Yodel CI 72 NON POD',
                                    'domestic' => true,
                                ],
                                '155' => [
                                    'name' => 'Yodel CI 48 POD',
                                    'domestic' => true,
                                ],
                                '180' => [
                                    'name' => 'Express 48 (NI)',
                                    'domestic' => true,
                                ],
                                '190' => [
                                    'name' => 'Express 24',
                                    'domestic' => true,
                                ],
                                '200' => [
                                    'name' => 'Saturday Priority 12:00',
                                    'domestic' => true,
                                ],
                                '220' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '260' => [
                                    'name' => 'Express Isle Exchange',
                                    'domestic' => true,
                                ],
                                '290' => [
                                    'name' => 'Express 24 Exchange',
                                    'domestic' => true,
                                ],
                                '310' => [
                                    'name' => '@HOME 24',
                                    'domestic' => true,
                                ],
                                '340' => [
                                    'name' => '@HOME 48',
                                    'domestic' => true,
                                ],
                                '350' => [
                                    'name' => '@HOME Return',
                                    'domestic' => true,
                                ],
                                '370' => [
                                    'name' => '@HOME Catalogue',
                                    'domestic' => true,
                                ],
                                '380' => [
                                    'name' => '@HOME 72',
                                    'domestic' => true,
                                ],
                                '390' => [
                                    'name' => '@HOME 72 (NI)',
                                    'domestic' => true,
                                ],
                                '510' => [
                                    'name' => 'Express 48 (NI) Exchange',
                                    'domestic' => true,
                                ],
                                '530' => [
                                    'name' => 'Express 24 (BT) Exchange',
                                    'domestic' => true,
                                ],
                                '540' => [
                                    'name' => 'Express 48 (UK) Exchange',
                                    'domestic' => true,
                                ],
                                '560' => [
                                    'name' => 'Exchange Return',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],
                                '10' => [
                                    'name' => 'Priority 12:00',
                                    'domestic' => true,
                                ],




//    730	@HOME 48 (NI)
//740	@HOME 24 (BT)
//750	@HOME 48 (BT)
//840	@HOME MINI
//850	EXPRESS SATURDAY
//860	XPRESS 24 POD
//861	XPRESS 24 NON POD
//862	XPRESS 48 POD
//863	XPRESS 48 NON POD
//864	XPRESS XS 48 NON POD
//870	XPECT 24 POD
//871	XPECT 24 NON POD
//872	XPECT 48 POD
//873	XPECT 48 NON POD
//874	XPECT 48 XL POD
//875	XPECT 48 XL NON POD
//876	XPECT SATURDAY POD
//877	XPECT SATURDAY NON POD
//878	XPECT 48 RETURN POD
//879	XPECT PRE 12 POD
//880	XPERT 24 POD DESK
//881	XPERT 24 ADDRESS ONLY
//882	XPERT 24 HVT POD
//883	XPERT 24 BFPO POD
//884	XPERT SATURDAY ADDRESS ONLY
//885	XPERT SATURDAY HVT POD
//886	XPERT SATURDAY POD EXCHANGE
//887	XPERT 24 POD EXCHANGE
//889	XPERT PRE 12 NON POD
//890	XPERT PRE 12 HVT ADDRESS ONLY
//891	XPERT PRE 12 ADDRESS ONLY
//892	XPECT PRE 12 SATURDAY POD
//893	XPERT PRE 12 SATURDAY NON POD
//894	XPERT PRE 12 SATURDAY ADDRESS ONLY
//895	XPERT PRE 12 SATURDAY HVT ADD ONLY
//896	XPERT CATALOGUE NON POD
//897	XPERT 24 RETURN POD
//898	XPRESS MINI 48 NON POD




                                'Express' => [
                                    'name' => 'Express',
                                    'domestic' => true,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => $ukMailAllSigAddOns
                                ],
                                'Yodel World' => [
                                    'name' => 'Yodel World',
                                    'domestic' => false,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => $ukMailAllSigAddOns
                                ],
                                'Yodel ROI' => [
                                    'name' => 'Yodel ROI',
                                    'domestic' => false,
                                    'packageTypes' => $ukMailBagitAndParcel,
                                    'addOns' => [
                                        [
                                            'title' => UKMailSignature::ADDRESS_ONLY,
                                            'default' => true
                                        ]
                                    ]
                                ],
                                'Yodel CI' => [
                                    'name' => 'Yodel CI',
                                    'domestic' => false,
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
