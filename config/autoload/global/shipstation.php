<?php

use CG\ShipStation\Carrier\Service;

return [
    'di' => [
        'instance' => [
            Service::class => [
                'parameters' => [
                    'carriersConfig' => [
                        'fedex' => [
                            'channelName' => 'fedex-ss',
                            'displayName' => 'FedEx',
                            'allowsCancellation' => true,
                            'allowsManifesting' => false,
                            'fields' => [
                                'nickname' => [
                                    'name' => 'nickname',
                                    'label' => 'Nickname',
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
                        ]
                    ]
                ]
            ]
        ]
    ]
];