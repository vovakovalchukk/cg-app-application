<?php

use CG\Channel\Service as ChannelService;
use CourierAdapter\Controller\AccountController;
use CourierAdapter\Module;
use Zend\Form\Element\Button;
use Zend\Form\Element\Hidden;

return [
    'forms' => [
        ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . 'CourierAdapter\Provider' => [
            'attributes' => [
                'id' => 'settings_account_form_courier_adapter',
            ],
            'options' => [
                'actionRoute' => implode('/', [Module::ROUTE, AccountController::ROUTE, AccountController::ROUTE_SAVE]),
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