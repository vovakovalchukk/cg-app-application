<?php

use CG\Hermes\Credentials\Request\TestPackGenerator;
use CG\Hermes\DeliveryService\Service as DeliveryServiceService;

return [
    'di' => [
        'instance' => [
            DeliveryServiceService::class => [
                'parameters' => [
                    'servicesConfig' => [
                        'Standard' => [
                            'displayName' => 'Standard service',
                        ],
                        'NextDay' => [
                            'displayName' => 'Next Day',
                            'nextDay' => true,
                            'countries' => [
                                'AT', 'BE', 'CZ', 'DK', 'FI', 'FR', 'DE', 'HU', 'IT', 'LU', 'MC', 'NL', 'PT', 'SK', 'ES', 'SE', 'GB',
                            ]
                        ],
                        'Sunday' => [
                            'displayName' => 'Sunday service',
                            'specificDay' => 7,
                        ]
                    ],
                    'defaultConfig' => [
                        'options' => [
                            'signature' => [
                                'countries' => [
                                    'AT', 'BE', 'CZ', 'DK', 'FI', 'FR', 'DE', 'HU', 'IT', 'LU', 'MC', 'NL', 'PT', 'SK', 'ES', 'SE', 'GB',
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            TestPackGenerator::class => [
                'parameters' => [
                    'shipmentsData' => [
                        [
                            'customerReference' => 'HTEST25',
                            'deliveryService' => 'Standard',
                            'signatureRequired' => false,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => '145 Alfreton Road', 'line2' => 'Little Eaton', 'line3' => 'Derby', 'line4' => '', 'line5' => '', 'postCode' => 'DE21 5AA', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 0.95]]
                        ],
                        [
                            'customerReference' => 'HTEST26',
                            'deliveryService' => 'Standard',
                            'signatureRequired' => false,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => '145 Alfreton Road', 'line2' => 'Little Eaton', 'line3' => 'Derby', 'line4' => '', 'line5' => '', 'postCode' => 'DE21 5AA', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 1.8]]
                        ],
                        [
                            'customerReference' => 'HTEST27',
                            'deliveryService' => 'Standard',
                            'signatureRequired' => true,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => '1 Brigg Road', 'line2' => 'Barton-Upon-Humber', 'line3' => '', 'line4' => '', 'line5' => '', 'postCode' => 'DN18 5DH', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 2.3]]
                        ],
                        [
                            'customerReference' => 'HTEST28',
                            'deliveryService' => 'Standard',
                            'signatureRequired' => false,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => '1 Lansbury Avenue', 'line2' => 'New Rossington', 'line3' => 'Doncaster', 'line4' => '', 'line5' => '', 'postCode' => 'DN11 0AA', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 2.3]]
                        ],
                        [
                            'customerReference' => 'HTEST29',
                            'deliveryService' => 'Standard',
                            'signatureRequired' => false,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => 'West Delivery Office', 'line2' => '175 Tapton Hill Road', 'line3' => 'Sheffield', 'line4' => '', 'line5' => '', 'postCode' => 'S10 1BQ', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 0.7]]
                        ],
                        [
                            'customerReference' => 'HTEST30',
                            'deliveryService' => 'Standard',
                            'signatureRequired' => false,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => '37 Barncliffe Crescent', 'line2' => 'Sheffield', 'line3' => '', 'line4' => '', 'line5' => '', 'postCode' => 'S10 4DA', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 2.3]]
                        ],
                        [
                            'customerReference' => 'HTEST31',
                            'deliveryService' => 'Standard',
                            'signatureRequired' => false,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => '4 Montys Meadow', 'line2' => 'Worksop', 'line3' => '', 'line4' => '', 'line5' => '', 'postCode' => 'S81 7DA', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 2.3]]
                        ],
                        [
                            'customerReference' => 'HTEST32',
                            'deliveryService' => 'NextDay',
                            'signatureRequired' => true,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => 'Dovecote Cottage', 'line2' => 'Hodsock', 'line3' => 'Worksop', 'line4' => '', 'line5' => '', 'postCode' => 'S81 0TF', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 2.3]]
                        ],
                        [
                            'customerReference' => 'HTEST33',
                            'deliveryService' => 'NextDay',
                            'signatureRequired' => true,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => '47 Handsworth Road', 'line2' => 'Sheffield', 'line3' => '', 'line4' => '', 'line5' => '', 'postCode' => 'S9 4AA', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 2.3]]
                        ],
                        [
                            'customerReference' => 'HTEST34',
                            'deliveryService' => 'Standard',
                            'signatureRequired' => false,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => '32 Brigg Road', 'line2' => 'Barton-Upon-Humber', 'line3' => '', 'line4' => '', 'line5' => '', 'postCode' => 'DN18 5DH', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 18]]
                        ],
                        [
                            'customerReference' => 'HTEST35',
                            'deliveryService' => 'Standard',
                            'signatureRequired' => false,
                            'deliveryAddress' => [
                                'firstName' => 'Test', 'lastName' => 'Parcel -  Do not Deliver', 'line1' => '19 Lansnury Avenue', 'line2' => 'New Rossington', 'line3' => 'Doncaster', 'line4' => '', 'line5' => '', 'postCode' => 'DN11 0AA', 'emailAddress' => 'it.integration@hermes-europe.co.uk'
                            ],
                            'packages' => [['weight' => 18]]
                        ],
                    ]
                ]
            ]
        ]
    ]
];