<?php

use CG\ShipStation\Account as AccountService;
use CG\ShipStation\Account\CreationService as AccountCreationService;
use CG\ShipStation\Account\Usps as UspsAccountConnector;
use CG\ShipStation\Account\Usps\Mapper as UspsAccountMapper;
use CG\ShipStation\Carrier\Label\Creator\Factory as LabelCreatorFactory;
use CG\ShipStation\Carrier\Service;
use CG\ShipStation\Client;
use CG\ShipStation\Webhook\Notification\StorageInterface as WebhookNotificationStorage;
use CG\ShipStation\Webhook\Notification\Storage\Redis as WebhookNotificationStorageRedis;
use Guzzle\Http\Client as GuzzleClient;
use CG\ShipStation\Carrier\Rates\Usps\ShipmentIdStorage;
use CG\ShipStation\PackageType\RoyalMail\Service as RoyalMailPackageTypeService;
use CG\ShipStation\PackageType\Usps\Service as UspsPackageTypeService;
use CG\ShipStation\ShippingService\RoyalMail as RoyalMailShippingService;
use CG\ShipStation\ShippingService\Usps as UspsShippingService;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                WebhookNotificationStorage::class => WebhookNotificationStorageRedis::class,
            ],
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
            LabelCreatorFactory::class => [
                'parameters' => [
                    // Don't use our FailoverClient, use Guzzle directly, as this is for talking to a third-party
                    'guzzleClient' => GuzzleClient::class,
                ]
            ],
            WebhookNotificationStorageRedis::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis'
                ]
            ],
            ShipmentIdStorage::class => [
                'parameters' => [
                    'redisClient' => 'reliable_redis'
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
                            'allowsRates' => false,
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
                                    'label' => 'State',
                                    'required' => true,
                                ],
                                'postal_code' => [
                                    'name' => 'postal_code',
                                    'label' => 'Zip code',
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
                                    'label' => 'Do you agree to <a href="https://app.shipengine.com/content/integration/FedEx/EULA.pdf" target="_blank">FedEx\'s EULA</a>?',
                                    'required' => true,
                                    'inputType' => 'checkbox',
                                ],
                            ]
                        ],
                        'ups-us' => [
                            'channelName' => 'ups-us-ss',
                            'displayName' => 'UPS (US)',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'allowsRates' => false,
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
                        'ups-uk' => [
                            'channelName' => 'ups-ss',
                            'displayName' => 'UPS (UK)',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'allowsRates' => false,
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
                            'featureFlag' => Service::FEATURE_FLAG_USPS,
                            'allowsCancellation' => true,
                            'allowsManifesting' => true,
                            'allowsRates' => true,
                            'fields' => [],
                            'bookingOptions' => [
                                'itemParcelAssignment' => 'itemParcelAssignment',
                                'weight' => 'weight',
                                'height' => 'height',
                                'width' => 'width',
                                'length' => 'length',
                                'cost' => 'cost',
                                'packageType' => 'packageType'
                            ]
                        ],
                        'royal-mail' => [
                            'channelName' => 'royal-mail-ss',
                            'displayName' => 'Royal Mail (OBA)',
                            'featureFlag' => Service::FEATURE_FLAG_ROYAL_MAIL,
                            'allowsCancellation' => true,
                            'allowsManifesting' => true,
                            'activationDelayed' => true,
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
                                'oba_email' => [
                                    'name' => 'oba_email',
                                    'label' => 'OBA Email Address',
                                    'required' => true,
                                    'inputType' => 'email',
                                ],
                                'contact_name' => [
                                    'name' => 'contact_name',
                                    'label' => 'Contact Name',
                                    'required' => true,
                                ],
                                'email' => [
                                    'name' => 'email',
                                    'label' => 'Contact Email Address',
                                    'required' => true,
                                    'inputType' => 'email',
                                ],
                                'street_line1' => [
                                    'name' => 'street_line1',
                                    'label' => 'Address Line 1',
                                    'required' => true,
                                ],
                                'street_line2' => [
                                    'name' => 'street_line2',
                                    'label' => 'Address Line 2',
                                    'required' => false,
                                ],
                                'city' => [
                                    'name' => 'city',
                                    'label' => 'City',
                                    'required' => true,
                                ],
                                'postal_code' => [
                                    'name' => 'postal_code',
                                    'label' => 'Post code',
                                    'required' => true,
                                ],
                                'phone' => [
                                    'name' => 'phone',
                                    'label' => 'Telephone',
                                    'required' => true,
                                    'inputType' => 'number',
                                ],
                            ],
                            'bookingOptions' => [
                                'weight' => 'weight',
                                'packageType' => 'packageType',
                            ],
                        ],
                        'fedex-uk' => [
                            'channelName' => 'fedex-uk-ss',
                            'displayName' => 'FedEx (UK)',
                            'featureFlag' => Service::FEATURE_FLAG_FEDEX_UK,
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'allowsRates' => false,
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
                                'state_province' => [
                                    'name' => 'state_province',
                                    'label' => 'County',
                                    'required' => true,
                                ],
                                'postal_code' => [
                                    'name' => 'postal_code',
                                    'label' => 'Post code',
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
                                    'label' => 'Do you agree to <a href="https://app.shipengine.com/content/integration/FedEx/EULA.pdf" target="_blank">FedEx\'s EULA</a>?',
                                    'required' => true,
                                    'inputType' => 'checkbox',
                                ],
                            ]
                        ],
                        'dhl-express-uk' => [
                            'channelName' => 'dhl-express-uk-ss',
                            'displayName' => 'DHL Express (UK)',
                            'featureFlag' => Service::FEATURE_FLAG_DHL_EXPRESS_UK,
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'allowsRates' => false,
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
                                'site_id' => [
                                    'name' => 'site_id',
                                    'label' => 'Site ID',
                                    'required' => true,
                                ],
                                'password' => [
                                    'name' => 'password',
                                    'label' => 'Password',
                                    'required' => true,
                                    'inputType' => 'password',
                                ],
                            ]
                        ],
                    ],
                    'defaultBookingOptions' => [
                        'parcels' => 'parcels',
                        'itemParcelAssignment' => 'itemParcelAssignment',
                        'weight' => 'weight',
                        'height' => 'height',
                        'width' => 'width',
                        'length' => 'length',
                        'harmonisedSystemCode' => 'harmonisedSystemCode',
                        'countryOfOrigin' => 'countryOfOrigin',
                    ]
                ]
            ],
            UspsPackageTypeService::class => [
                'parameters' => [
                    'packageTypesConfig' => [
                        // Measurements stored in inches and ounces, keep these in order from smallest to largest within each service
                        'Domestic' => [
                            'usps_first_class_mail' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                    'code' => 'letter',
                                ],
                                'Large Envelope Or Flat' => [
                                    'weight' => 15.9,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                    'code' => 'large_envelope_or_flat',
                                ],
                                'Package' => [
                                    'weight' => 15.9,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'package',
                                ],
                                'Thick Envelope' => [
                                    'weight' => 15.9,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'thick_envelope',
                                ],
                            ],
                            'usps_parcel_select' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                    'code' => 'letter',
                                ],
                                'Large Envelope Or Flat' => [
                                    'weight' => 15.9,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                    'code' => 'large_envelope_or_flat',
                                ],
                                'Package' => [
                                    'weight' => 15.9,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'package',
                                ],
                                'Thick Envelope' => [
                                    'weight' => 15.9,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'thick_envelope',
                                ],
                            ],
                            'usps_media_mail' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                    'code' => 'letter',
                                ],
                                'Large Envelope Or Flat' => [
                                    'weight' => 15.9,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                    'code' => 'large_envelope_or_flat',
                                ],
                                'Package' => [
                                    'weight' => 15.9,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'package',
                                ],
                                'Thick Envelope' => [
                                    'weight' => 15.9,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'thick_envelope',
                                ],
                            ],
                            'usps_priority_mail' => [
                                'Letter' => [
                                    'weight' => 1120,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                    'code' => 'letter',
                                ],
                                'Large Envelope Or Flat' => [
                                    'weight' => 1120,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                    'code' => 'large_envelope_or_flat',
                                ],
                                'Package' => [
                                    'weight' => 1120,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'package',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                                'Thick Envelope' => [
                                    'weight' => 1120,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'thick_envelope',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                                'Flat Rate Envelope' => [
                                    'weight' => 1120,
                                    'length' => 12.5,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_envelope',
                                ],
                                'Flat Rate Padded Envelope' => [
                                    'weight' => 1120,
                                    'length' => 9.5,
                                    'width' => 12.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_padded_envelope',
                                ],
                                'Legal Flat Rate Envelope' => [
                                    'weight' => 1120,
                                    'length' => 15,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rat_legal_envelope',
                                ],
                                'Small Flat Rate Box' => [
                                    'weight' => 1120,
                                    'length' => 8.625,
                                    'width' => 5.375,
                                    'height' => 1.625,
                                    'code' => 'small_flat_rate_box',
                                ],
                                'Medium Flat Rate Box' => [
                                    'weight' => 1120,
                                    'length' => 13.625,
                                    'width' => 11.875,
                                    'height' => 3.375,
                                    'code' => 'medium_flat_rate_box',
                                ],
                                'Large Flat Rate Boxx' => [
                                    'weight' => 1120,
                                    'length' => 12,
                                    'width' => 12,
                                    'height' => 5.5,
                                    'code' => 'large_flat_rate_box',
                                ],
                                'Regional Rate Box A1' => [
                                    'weight' => 240,
                                    'length' => 10,
                                    'width' => 7,
                                    'height' => 4.75,
                                    'code' => 'regional_rate_box_a',
                                ],
                                'Regional Rate Box A2' => [
                                    'weight' => 240,
                                    'length' => 10.9375,
                                    'width' => 12.8125,
                                    'height' => 2.375,
                                    'code' => 'regional_rate_box_a',
                                ],
                                'Regional Rate Box B1' => [
                                    'weight' => 320,
                                    'length' => 12,
                                    'width' => 10.25,
                                    'height' => 5,
                                    'code' => 'regional_rate_box_b',
                                ],
                                'Regional Rate Box B2' => [
                                    'weight' => 320,
                                    'length' => 15.875,
                                    'width' => 14.375,
                                    'height' => 2.875,
                                    'code' => 'regional_rate_box_b',
                                ],
                            ],
                            'usps_priority_mail_express' => [
                                'Flat Rate Envelope' => [
                                    'weight' => 1120,
                                    'length' => 12.5,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_envelope',
                                ],
                                'Flat Rate Padded Envelope' => [
                                    'weight' => 1120,
                                    'length' => 9.5,
                                    'width' => 12.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_padded_envelope',
                                ],
                                'Legal Flat Rate Envelope' => [
                                    'weight' => 1120,
                                    'length' => 15,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rat_legal_envelope',
                                ],
                            ]
                        ],
                        'International' => [
                            'usps_first_class_mail_international' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                    'code' => 'Letter',
                                ],
                                'Large Envelope Or Flat' => [
                                    'weight' => 64,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                    'code' => 'large_envelope_or_flat',
                                ],
                                'Package' => [
                                    'weight' => 64,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'Package',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                                'Thick Envelope' => [
                                    'weight' => 64,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'Package',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                            ],
                            'usps_parcel_select_international' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                    'code' => 'Letter',
                                ],
                                'Large Envelope Or Flat' => [
                                    'weight' => 64,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                    'code' => 'large_envelope_or_flat',
                                ],
                                'Package' => [
                                    'weight' => 64,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'Package',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                                'Thick Envelope' => [
                                    'weight' => 64,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'Package',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                            ],
                            'usps_media_mail_international' => [
                                'Letter' => [
                                    'weight' => 3.5,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                    'code' => 'letter',
                                ],
                                'Large Envelope Or Flat' => [
                                    'weight' => 64,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                    'code' => 'large_envelope_or_flat',
                                ],
                                'Package' => [
                                    'weight' => 64,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'package',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                                'Thick Envelope' => [
                                    'weight' => 64,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'thick_envelope',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                            ],
                            'usps_priority_mail_international' => [
                                'Letter' => [
                                    'weight' => 1120,
                                    'length' => 11.5,
                                    'width' => 6.125,
                                    'height' => 0.25,
                                    'code' => 'letter',
                                ],
                                'Large Envelope Or Flat' => [
                                    'weight' => 1056,
                                    'length' => 15,
                                    'width' => 12,
                                    'height' => 0.75,
                                    'code' => 'large_envelope_or_flat',
                                ],
                                'Package' => [
                                    'weight' => 1120,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'package',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                                'Thick Envelope' => [
                                    'weight' => 1120,
                                    'length' => 61,
                                    'width' => 46,
                                    'height' => 46,
                                    'code' => 'thick_envelope',
                                    'restrictionType' => UspsPackageTypeService::LENGTH_AND_GIRTH_RESTRICTION_TYPE,
                                ],
                                'Flat Rate Envelope' => [
                                    'weight' => 64,
                                    'length' => 12.5,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_envelope',
                                ],
                                'Flat Rate Padded Envelope' => [
                                    'weight' => 64,
                                    'length' => 9.5,
                                    'width' => 12.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_padded_envelope',
                                ],
                                'Legal Flat Rate Envelope' => [
                                    'weight' => 64,
                                    'length' => 15,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_legal_envelope',
                                ],
                                'Small Flat Rate Box' => [
                                    'weight' => 320,
                                    'length' => 8.625,
                                    'width' => 5.375,
                                    'height' => 1.625,
                                    'code' => 'small_flat_rate_box',
                                ],
                                'Medium Flat Rate Box' => [
                                    'weight' => 320,
                                    'length' => 13.625,
                                    'width' => 11.875,
                                    'height' => 3.375,
                                    'code' => 'medium_flat_rate_box',
                                ],
                                'Large Flat Rate Box' => [
                                    'weight' => 320,
                                    'length' => 12,
                                    'width' => 12,
                                    'height' => 5.5,
                                    'code' => 'large_flat_rate_box',
                                ],
                            ],
                            'usps_priority_mail_express_international' => [
                                'Flat Rate Envelope' => [
                                    'weight' => 64,
                                    'length' => 12.5,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_envelope',
                                ],
                                'Flat Rate Padded Envelope' => [
                                    'weight' => 64,
                                    'length' => 9.5,
                                    'width' => 12.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_padded_envelope',
                                ],
                                'Legal Flat Rate Envelope' => [
                                    'weight' => 64,
                                    'length' => 15,
                                    'width' => 9.5,
                                    'height' => 3, // No height provided, set at reasonable size for envelope
                                    'code' => 'flat_rate_legal_envelope',
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            UspsShippingService::class => [
                'parameters' => [
                    'servicesConfig' => [
                        [
                            'service_code' => 'usps_first_class_mail',
                            'name' => 'USPS First Class Mail',
                            'domestic' => true,
                            'international' => false,
                            'is_multi_package_supported' => false,
                        ],
                        [
                            'service_code' => 'usps_media_mail',
                            'name' => 'USPS Media Mail',
                            'domestic' => true,
                            'international' => false,
                            'is_multi_package_supported' => false,
                        ],
                        [
                            'service_code' => 'usps_parcel_select',
                            'name' => 'USPS Parcel Select Ground',
                            'domestic' => true,
                            'international' => false,
                            'is_multi_package_supported' => false,
                        ],
                        [
                            'service_code' => 'usps_priority_mail',
                            'name' => 'USPS Priority Mail',
                            'domestic' => true,
                            'international' => false,
                            'is_multi_package_supported' => false,
                        ],
                        [
                            'service_code' => 'usps_priority_mail_express',
                            'name' => 'USPS Priority Mail Express',
                            'domestic' => true,
                            'international' => false,
                            'is_multi_package_supported' => false,
                        ],
                        [
                            'service_code' => 'usps_first_class_mail_international',
                            'name' => 'USPS First Class Mail Intl',
                            'domestic' => false,
                            'international' => true,
                            'is_multi_package_supported' => false,
                        ],
                        [
                            'service_code' => 'usps_priority_mail_international',
                            'name' => 'USPS Priority Mail Intl',
                            'domestic' => false,
                            'international' => true,
                            'is_multi_package_supported' => false,
                        ],
                        [
                            'service_code' => 'usps_priority_mail_express_international',
                            'name' => 'USPS Priority Mail Express Intl',
                            'domestic' => false,
                            'international' => true,
                            'is_multi_package_supported' => false,
                        ],
                    ]
                ]
            ],
            RoyalMailPackageTypeService::class => [
                'parameters' => [
                    // Measurements in kg and cm
                    // Keep these in size order, smallest to biggest
                    'domesticConfig' => [
                        'Letter' => [
                            'name' => 'Letter',
                            'code' => 'letter',
                            'weight' => 0.1,
                            'length' => 24,
                            'width' => 16.5,
                            'height' => 0.5,
                        ],
                        'Large Letter' => [
                            'name' => 'Large Letter',
                            'code' => 'large_letter',
                            'weight' => 0.75,
                            'length' => 35.3,
                            'width' => 25,
                            'height' => 2.5,
                        ],
                        'Parcel' => [
                            'name' => 'Parcel',
                            'code' => 'parcel',
                            'weight' => 15,
                            'length' => 61,
                            'width' => 46,
                            'height' => 46,
                        ],
                    ],
                    'internationalConfig' => [
                        'Letter' => [
                            'name' => 'Letter',
                            'code' => 'letter',
                            'weight' => 0.1,
                            'length' => 24,
                            'width' => 16.5,
                            'height' => 0.5,
                        ],
                        'Large Letter' => [
                            'name' => 'Large Letter',
                            'code' => 'large_letter',
                            'weight' => 0.5,
                            'length' => 38.1,
                            'width' => 30.5,
                            'height' => 2,
                        ],
                        'Parcel' => [
                            'name' => 'Parcel',
                            'code' => 'parcel',
                            'weight' => 2,
                            'length' => 60,
                            'width' => 60,
                            'height' => 60,
                        ],
                        /* This doesnt seem to be supported by ShipStation, here in case they enable it
                        'Printed Papers' => [
                            'weight' => 5,
                            'length' => 60,
                            'width' => 60,
                            'height' => 60,
                        ],
                        */
                    ]
                ]
            ],
            RoyalMailShippingService::class => [
                'parameters' => [
                    'signatureServices' => [
                        'rm_special_delivery_1pm_500',
                        'rm_special_delivery_1pm_750',
                        'rm_special_delivery_1pm_1000',
                        'rm_special_delivery_1pm_2500',
                        'rm_special_delivery_9am_50',
                        'rm_special_delivery_9am_750',
                        'rm_special_delivery_9am_1000',
                        'rm_special_delivery_9am_2500',
                        'rm_special_delivery_1pm_500_sg',
                        'rm_special_delivery_1pm_1000_sg',
                        'rm_special_delivery_1pm_2500_sg',
                        'rm_special_delivery_9am_50_sg',
                        'rm_special_delivery_9am_1000_sg',
                        'rm_special_delivery_9am_2500_sg',
                        'rm_special_delivery_1pm_500_lcsg',
                        'rm_special_delivery_1pm_1000_lcsg',
                        'rm_special_delivery_1pm_2500_lcsg',
                        'rm_special_delivery_9am_50_lcsg',
                        'rm_special_delivery_9am_1000_lcsg',
                        'rm_special_delivery_9am_2500_lcsg',
                        'rm_special_delivery_1pm_500_sg',
                        'rm_special_delivery_1pm_1000_sg',
                        'rm_special_delivery_1pm_2500_sg',
                        'rm_special_delivery_9am_50_sg',
                        'rm_special_delivery_9am_1000_sg',
                        'rm_special_delivery_9am_2500_sg',
                        'rm_special_delivery_1pm_500_lcsg',
                        'rm_special_delivery_1pm_1000_lcsg',
                        'rm_special_delivery_1pm_2500_lcsg',
                        'rm_special_delivery_9am_50_lcsg',
                        'rm_special_delivery_9am_1000_lcsg',
                        'rm_special_delivery_9am_2500_lcsg',
                        'rm_1st_class_signed_for',
                        'rm_2nd_class_signed_for',
                        'rm_hm_forces_signed_for',
                        'rm_hm_forces_special_delivery',
                        'rm_hm_forces_special_delivery_1000',
                        'rm_hm_forces_special_delivery_2500',
                        'rm_intl_business_mail_signed_zonal',
                        'rm_intl_business_mail_signed_country',
                        'rm_intl_business_mail_signed_extra_zonal',
                        'rm_intl_business_mail_signed_extra_country',
                        'rm_intl_business_parcels_signed_zonal',
                        'rm_intl_business_parcels_signed_country',
                        'rm_intl_business_parcels_signed_extra_zonal',
                        'rm_intl_business_parcels_signed_extra_country',
                        'rm_intl_signed_on_account',
                        'rm_intl_signed_on_account_extra',
                        'rm_intl_business_mail_tracked_signed_zonal',
                        'rm_intl_business_mail_tracked_signed_country',
                        'rm_intl_business_mail_tracked_signed_extra_zonal',
                        'rm_intl_business_mail_tracked_signed_extra_country',
                        'rm_intl_business_parcels_tracked_signed_zonal',
                        'rm_intl_business_parcels_tracked_signed_country',
                        'rm_intl_business_parcels_tracked_signed_extra_zonal',
                        'rm_intl_business_parcels_tracked_signed_extra_country',
                        'rm_intl_tracked_signed_on_account',
                        'rm_intl_tracked_signed_on_account_extra',
                        'rm_hm_forces_signed_for',
                        'rm_intl_business_parcels_tracked_signed_country_hi_vol',
                        'rm_intl_business_parcel_signed_country_hi_vol',
                        'rm_intl_business_parcel_tracked_signed_extra_country_hi_vol',
                        'rm_intl_business_parcel_signed_extra_country_hi_vol',
                        'rm_intl_business_mail_tracked_signed_letter_hi_vol',
                        'rm_intl_business_mail_tracked_signed_letter_extra_comp_country_hi_vol',
                        'rm_intl_business_mail_signed_letter_country_hi_vol',
                        'rm_intl_business_mail_signed_letter_extra_comp_country_hi_vol',
                    ]
                ]
            ]
        ]
    ]
];
