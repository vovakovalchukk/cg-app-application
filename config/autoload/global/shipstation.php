<?php

use CG\ShipStation\Account as AccountService;
use CG\ShipStation\Account\CreationService as AccountCreationService;
use CG\ShipStation\Account\Usps as UspsAccountConnector;
use CG\ShipStation\Carrier\Label\Creator as LabelCreator;
use CG\ShipStation\Carrier\Service;
use CG\ShipStation\Client;
use CG\ShipStation\Account\Usps\Mapper as UspsAccountMapper;
use Guzzle\Http\Client as GuzzleClient;
use CG\ShipStation\PackageType\Service as PackageTypeService;

return [
    'di' => [
        'instance' => [
            AccountService::class => [
                'parameters' => [
                    'cryptor' => 'shipstation_cryptor',
                ]
            ],
            Client::class => [
                'parameters' => [
                    'cryptor' => 'shipstation_cryptor',
                    // Don't use our FailoverClient, use Guzzle directly, as this is for talking to a third-party
                    'guzzle' => GuzzleClient::class,
                ]
            ],
            AccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'shipstation_cryptor',
                ]
            ],
            UspsAccountMapper::class => [
                'parameters' => [
                    'cryptor' => 'shipstation_cryptor',
                ]
            ],
            UspsAccountConnector::class => [
                'parameters' => [
                    'cryptor' => 'shipstation_cryptor',
                ]
            ],
            LabelCreator::class => [
                'parameters' => [
                    // Don't use our FailoverClient, use Guzzle directly, as this is for talking to a third-party
                    'guzzleClient' => GuzzleClient::class,
                ]
            ],
            Service::class => [
                'parameters' => [
                    'carriersConfig' => [
                        'fedex' => [
                            'channelName' => 'fedex-ss',
                            'displayName' => 'FedEx (US)',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'fields' => [
                                'nickname' => [
                                    'name' => 'nickname',
                                    'label' => 'Nickname for account',
                                    'required' => true,
                                ],
                                'account_number' => [
                                    'name' => 'account_number',
                                    'label' => 'Account Number',
                                    'required' => true,
                                ],
                                'first_name' => [
                                    'name' => 'first_name',
                                    'label' => 'First Name',
                                    'required' => true,
                                ],
                                'last_name' => [
                                    'name' => 'last_name',
                                    'label' => 'Last Name',
                                    'required' => true,
                                ],
                                'company' => [
                                    'name' => 'company',
                                    'label' => 'Company',
                                    'required' => false,
                                ],
                                'address1' => [
                                    'name' => 'address1',
                                    'label' => 'Address Line 1',
                                    'required' => true,
                                ],
                                'address2' => [
                                    'name' => 'address2',
                                    'label' => 'Address Line 2',
                                    'required' => false,
                                ],
                                'city' => [
                                    'name' => 'city',
                                    'label' => 'City',
                                    'required' => true,
                                ],
                                'state' => [
                                    'name' => 'state',
                                    'label' => 'County / State',
                                    'required' => true,
                                ],
                                'postal_code' => [
                                    'name' => 'postal_code',
                                    'label' => 'Post / zip code',
                                    'required' => true,
                                ],
                                'country_code' => [
                                    'name' => 'country_code',
                                    'label' => 'Country',
                                    'required' => true,
                                    'inputType' => 'country',
                                ],
                                'email' => [
                                    'name' => 'email',
                                    'label' => 'Email',
                                    'required' => true,
                                    'inputType' => 'email',
                                ],
                                'phone' => [
                                    'name' => 'phone',
                                    'label' => 'Telephone',
                                    'required' => true,
                                    'inputType' => 'number',
                                ],
                                'agree_to_eula' => [
                                    'name' => 'agree_to_eula',
                                    'label' => 'Do you agree to FedEx\'s EULA?',
                                    'required' => true,
                                    'inputType' => 'checkbox',
                                ],
                            ]
                        ],
                        'ups' => [
                            'channelName' => 'ups-ss',
                            'displayName' => 'UPS (US)',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'fields' => [
                                'nickname' => [
                                    'name' => 'nickname',
                                    'label' => 'Nickname for account',
                                    'required' => true,
                                ],
                                'account_number' => [
                                    'name' => 'account_number',
                                    'label' => 'Account Number',
                                    'required' => true,
                                ],
                                'acount_country_code' => [
                                    'name' => 'account_country_code',
                                    'label' => 'Account Country',
                                    'required' => true,
                                    'inputType' => 'country',
                                ],
                                'account_postal_code' => [
                                    'name' => 'account_postal_code',
                                    'label' => 'Account post / zip code',
                                    'required' => true,
                                ],
                                'title' => [
                                    'name' => 'title',
                                    'label' => 'Title',
                                    'required' => false,
                                ],
                                'first_name' => [
                                    'name' => 'first_name',
                                    'label' => 'First Name',
                                    'required' => true,
                                ],
                                'last_name' => [
                                    'name' => 'last_name',
                                    'label' => 'Last Name',
                                    'required' => true,
                                ],
                                'company' => [
                                    'name' => 'company',
                                    'label' => 'Company',
                                    'required' => false,
                                ],
                                'address1' => [
                                    'name' => 'address1',
                                    'label' => 'Address Line 1',
                                    'required' => true,
                                ],
                                'address2' => [
                                    'name' => 'address2',
                                    'label' => 'Address Line 2',
                                    'required' => false,
                                ],
                                'city' => [
                                    'name' => 'city',
                                    'label' => 'City',
                                    'required' => true,
                                ],
                                'state' => [
                                    'name' => 'state',
                                    'label' => 'County / State',
                                    'required' => true,
                                ],
                                'postal_code' => [
                                    'name' => 'postal_code',
                                    'label' => 'Post / zip code',
                                    'required' => true,
                                ],
                                'country_code' => [
                                    'name' => 'country_code',
                                    'label' => 'Country',
                                    'required' => true,
                                    'inputType' => 'country',
                                ],
                                'email' => [
                                    'name' => 'email',
                                    'label' => 'Email',
                                    'required' => true,
                                    'inputType' => 'email',
                                ],
                                'phone' => [
                                    'name' => 'phone',
                                    'label' => 'Telephone',
                                    'required' => true,
                                    'inputType' => 'number',
                                ],
                                'control_id' => [
                                    'name' => 'control_id',
                                    'label' => 'Control ID',
                                    'required' => true,
                                    'inputType' => 'text',
                                ],
                                'invoice_number' => [
                                    'name' => 'invoice_number',
                                    'label' => 'Latest Invoice Number',
                                    'required' => true,
                                    'inputType' => 'text',
                                ],
                                'invoice_amount' => [
                                    'name' => 'invoice_amount',
                                    'label' => 'Latest Invoice Amount',
                                    'required' => true,
                                    'inputType' => 'number',
                                ],
                                'invoice_date' => [
                                    'name' => 'invoice_date',
                                    'label' => 'Latest Invoice Date',
                                    'required' => true,
                                    'inputType' => 'date',
                                ],
                                'agree_to_technology_agreement' => [
                                    'name' => 'agree_to_technology_agreement',
                                    'label' => 'Do you agree to the UPS Technology Agreement?',
                                    'required' => true,
                                    'inputType' => 'checkbox',
                                ],
                            ]
                        ],
                        'usps' => [
                            'channelName' => 'usps-ss',
                            'displayName' => 'USPS',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'fields' => [],
                            'bookingOptions' => [
                                'weight' => 'weight',
                                'height' => 'height',
                                'width' => 'width',
                                'length' => 'length',
                                'cost' => 'cost',
                                'packageType' => 'packageType'
                            ]
                        ],
                    ],
                    'defaultBookingOptions' => [
                        'parcels' => 'parcels',
                        'weight' => 'weight',
                        'height' => 'height',
                        'width' => 'width',
                        'length' => 'length',
                    ]
                ]
            ],
            PackageTypeService::class => [
                'parameters' => [
                    'packageTypesConfig' => [
                        // Measurements stored in inches and ounces
                        'Domestic' => [
                            'usps_first_class_mail' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                ],
                                'Large Envelope' => [
                                    'weight' => 15.9,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                ],
                                'Package' => [
                                    'weight' => 15.9,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                ]
                            ],
                            'usps_media_mail' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                ],
                                'Large Envelope' => [
                                    'weight' => 15.9,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                ],
                                'Package' => [
                                    'weight' => 15.9,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                ]
                            ],
                            'usps_priority_mail' => [
                                'Letter' => [
                                    'weight' => 1120,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                ],
                                'Large Envelope' => [
                                    'weight' => 1120,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                ],
                                'Package' => [
                                    'weight' => 1120,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'restrictionType' => PackageTypeService::USPS_LENGTH_AND_GIRTH_RESTRICTION_TYPE
                                ],
                                'Flat Rate Envelope' => [
                                    'weight' => 1120,
                                    'length' => 12.5,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                ],
                                'Flat Rate Padded Envelope' => [
                                    'weight' => 1120,
                                    'length' => 9.5,
                                    'width' => 12.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                ],
                                'Legal Flat Rate Envelope' => [
                                    'weight' => 1120,
                                    'length' => 15,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                ],
                                'Small Flat Rate Box' => [
                                    'weight' => 1120,
                                    'length' => 8.625,
                                    'width' => 5.375,
                                    'height' => 1.625,
                                ],
                                'Medium Flat Rate Box' => [
                                    'weight' => 1120,
                                    'length' => 13.625,
                                    'width' => 11.875,
                                    'height' => 3.375,
                                ],
                                'Large Flat Rate Box' => [
                                    'weight' => 1120,
                                    'length' => 12,
                                    'width' => 12,
                                    'height' => 5.5,
                                ],
                                'Regional Rate Box A1' => [
                                    'weight' => 240,
                                    'length' => 10,
                                    'width' => 7,
                                    'height' => 4.75,
                                ],
                                'Regional Rate Box A2' => [
                                    'weight' => 240,
                                    'length' => 10.9375,
                                    'width' => 12.8125,
                                    'height' => 2.375,
                                ],
                                'Regional Rate Box B1' => [
                                    'weight' => 320,
                                    'length' => 12,
                                    'width' => 10.25,
                                    'height' => 5,
                                ],
                                'Regional Rate Box B2' => [
                                    'weight' => 320,
                                    'length' => 15.875,
                                    'width' => 14.375,
                                    'height' => 2.875,
                                ]
                            ]
                        ],
                        'International' => [
                            'usps_first_class_mail' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                ],
                                'Large Envelope' => [
                                    'weight' => 64,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                ],
                                'Package' => [
                                    'weight' => 64,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'restrictionType' => PackageTypeService::USPS_LENGTH_AND_GIRTH_RESTRICTION_TYPE
                                ]
                            ],
                            'usps_media_mail' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                ],
                                'Large Envelope' => [
                                    'weight' => 64,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                ],
                                'Package' => [
                                    'weight' => 64,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'restrictionType' => PackageTypeService::USPS_LENGTH_AND_GIRTH_RESTRICTION_TYPE
                                ]
                            ],
                            'usps_priority_mail' => [
                                'Letter' => [
                                    'weight' => 1120,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                ],
                                'Large Envelope' => [
                                    'weight' => 1056,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                ],
                                'Package' => [
                                    'weight' => 1120,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'restrictionType' => PackageTypeService::USPS_LENGTH_AND_GIRTH_RESTRICTION_TYPE
                                ],
                                'Flat Rate Envelope' => [
                                    'weight' => 64,
                                    'length' => 12.5,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                ],
                                'Flat Rate Padded Envelope' => [
                                    'weight' => 64,
                                    'length' => 9.5,
                                    'width' => 12.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                ],
                                'Legal Flat Rate Envelope' => [
                                    'weight' => 64,
                                    'length' => 15,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                ],
                                'Small Flat Rate Box' => [
                                    'weight' => 320,
                                    'length' => 8.625,
                                    'width' => 5.375,
                                    'height' => 1.625,
                                ],
                                'Medium Flat Rate Box' => [
                                    'weight' => 320,
                                    'length' => 13.625,
                                    'width' => 11.875,
                                    'height' => 3.375,
                                ],
                                'Large Flat Rate Box' => [
                                    'weight' => 320,
                                    'length' => 12,
                                    'width' => 12,
                                    'height' => 5.5,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];