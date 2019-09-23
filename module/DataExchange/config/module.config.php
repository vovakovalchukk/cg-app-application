<?php

use DataExchange\Controller\IndexController;
use DataExchange\Controller\EmailAccountController;
use DataExchange\Controller\FtpAccountController;
use DataExchange\Navigation\Factory as DataExchangeNavigation;
use DataExchange\Module;
use Zend\Mvc\Router\Http\Literal;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
    ],
    'navigation' => [
        'application-navigation' => [
            'data-exchange' => [
                'label'  => 'Data Exchange',
                'sprite' => 'sprite-exchange-white-18',
                'order'  => 17,
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . '/dataExchange',
                'feature-flag' => Module::FEATURE_FLAG,
            ]
        ],
        'data-exchange-navigation' => [
            'Accounts' => [
                'label' => 'Accounts',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    'Ftp' => [
                        'label' => 'FTP',
                        'title' => 'FTP',
                        'route' => Module::ROUTE . '/' . FtpAccountController::ROUTE
                    ],
                    'Email' => [
                        'label' => 'Email',
                        'title' => 'Email',
                        'route' => Module::ROUTE . '/' . EmailAccountController::ROUTE
                    ],
                ]
            ],
        ]
    ],
    'service_manager' => [
        'factories' => [
            'data-exchange-navigation'  => DataExchangeNavigation::class,
        ]
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/dataExchange',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'sub-header' => false,
                        'sidebar' => Module::TEMPLATE_SIDEBAR
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    FtpAccountController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/accounts/ftp',
                            'defaults' => [
                                'controller' => FtpAccountController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            FtpAccountController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save'
                                    ]
                                ],
                            ],
                            FtpAccountController::ROUTE_REMOVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/remove',
                                    'defaults' => [
                                        'action' => 'remove'
                                    ]
                                ],
                            ],
                            FtpAccountController::ROUTE_TEST => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/test',
                                    'defaults' => [
                                        'action' => 'test'
                                    ]
                                ],
                            ],
                        ]
                    ],
                    EmailAccountController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/accounts/email',
                            'defaults' => [
                                'controller' => EmailAccountController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            EmailAccountController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save'
                                    ]
                                ],
                            ],
                        ]
                    ],
                ]
            ]
        ],
    ],
];
