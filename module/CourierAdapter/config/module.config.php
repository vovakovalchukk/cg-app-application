<?php

use CG\CourierAdapter\Provider\Account as CAAccountService;
use CourierAdapter\Controller\AccountController;
use CourierAdapter\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [

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
                    ]
                ]
            ]
        ],
    ],
    'di' => [
        'instance' => [

        ],
    ],
];