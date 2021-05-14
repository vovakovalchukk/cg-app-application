<?php

use CG\Channel\Service as ChannelService;
use CG\CourierAdapter\Provider\Account as CAAccountService;
use CG\CourierAdapter\Provider\Account\CreationService as AccountCreationService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CourierAdapter\Account\Service as CAModuleAccountService;
use CourierAdapter\Controller\AccountController;
use CourierAdapter\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;
use CourierAdapter\Account\Email\Service as CASupportEmailService;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [
            ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . 'CourierAdapter\Provider' => dirname(__DIR__) . '/view/courier-adapter/settings_account.phtml',
        ]
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/carrier',
                    'defaults' => [],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    AccountController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/account',
                            'defaults' => [
                                'controller' => AccountController::class,
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            AccountController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/ca-save',
                                    'defaults' => [
                                        'action' => 'save',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            AccountController::ROUTE_SAVE_CONFIG => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/ca-save-config',
                                    'defaults' => [
                                        'action' => 'saveConfig',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            AccountController::ROUTE_TEST_PACK_FILE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/ca-test-pack-file',
                                    'defaults' => [
                                        'action' => 'downloadTestPackFile',
                                        'sidebar' => false,
                                        'subHeader' => false,
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            AccountController::ROUTE_REQUEST_CONNECTION => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/ca-request-connection',
                                    'defaults' => [
                                        'action' => 'requestConnection',
                                    ]
                                ],
                                'may_terminate' => true,
                            ]
                        ],
                    ],
                    CAAccountService::ROUTE_SETUP => [
                        'type' => Segment::class,
                        'options' => [
                            // Can't just use /setup/:channel as that conflicts with the dataplug module
                            'route' => '/setup/ca/:channel',
                            'defaults' => [
                                'controller' => AccountController::class,
                                'action' => 'setup',
                                'sidebar' => false,
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                    CAAccountService::ROUTE_REQUEST => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/request/ca/:channel',
                            'defaults' => [
                                'controller' => AccountController::class,
                                'action' => 'requestCredentials',
                                'sidebar' => false,
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            AccountController::ROUTE_REQUEST_SEND => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/send',
                                    'defaults' => [
                                        'controller' => AccountController::class,
                                        'action' => 'sendCredentialsRequest',
                                        'sidebar' => false,
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ]
                    ],
                    CAAccountService::ROUTE_AUTH_SUCCESS => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/auth/ca/:channel/success',
                            'defaults' => [
                                'controller' => AccountController::class,
                                'action' => 'authSuccess',
                                'sidebar' => false,
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                    CAAccountService::ROUTE_AUTH_FAILURE => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/auth/ca/:channel/failure',
                            'defaults' => [
                                'controller' => AccountController::class,
                                'action' => 'authFailure',
                                'sidebar' => false,
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                ]
            ]
        ],
    ],
    'di' => [
        'instance' => [
            AccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'courieradapter_cryptor'
                ]
            ],
            CAAccountMapper::class => [
                'parameters' => [
                    'cryptor' => 'courieradapter_cryptor'
                ]
            ],
            CAModuleAccountService::class => [
                'parameters' => [
                    'cryptor' => 'courieradapter_cryptor'
                ]
            ],
            CASupportEmailService::class => [
                'parameters' => [
                    'mailer' => 'orderhub-mailer'
                ]
            ]
        ],
    ],
];