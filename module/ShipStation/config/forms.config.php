<?php

use CG\Channel\Service as ChannelService;
use ShipStation\Controller\AccountController;
use ShipStation\Module;
use Zend\Form\Element\Button;
use Zend\Form\Element\Hidden;

return [
    'forms' => [
        ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . 'Shipstation' => [
            'attributes' => [
                'id' => 'settings_account_form_ss',
            ],
            'options' => [
                //'actionRoute' => implode('/', [Module::ROUTE, AccountController::ROUTE, AccountController::ROUTE_SAVE]),
            ],
            'elements' => [
                [
                    'spec' => [
                        'name' => 'account',
                        'type' => Hidden::class,
                    ],
                ],
                [
                    'spec' => [
                        'name' => 'route',
                        'type' => Hidden::class,
                    ],
                ],
                [
                    'spec' => [
                        'name' => 'renew',
                        'options'=> [
                            'label' => 'Connection'
                        ],
                        'type' => Button::class,
                        'attributes' => [
                            'value' => 'Renew Connection',
                            'class' => 'button',
                            'type' => 'button',
                            'id' => 'renew',
                        ],
                    ]
                ],
            ],
        ],
    ],
];