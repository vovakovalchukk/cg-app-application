<?php

use SetupWizard\Controller\MessagesController;
use SetupWizard\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'navigation' => [
        'setup-navigation' => [
            'Steps' => [
                'pages' => [
                    'messages' => [
                        'label' => 'Customer Messages',
                        'title' => 'Customer Messages',
                        'route' => Module::ROUTE . '/' . MessagesController::ROUTE_MESSAGE,
                        'order' => 25,
                        'sprite' => 'sprite-messages-circle-25-white',
                        'link' => false,
                    ],
                ]
            ],
        ],
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'child_routes' => [
                    MessagesController::ROUTE_MESSAGE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/messages',
                            'defaults' => [
                                'controller' => MessagesController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            MessagesController::ROUTE_SETUP => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/setup/:account',
                                    'defaults' => [
                                        'controller' => MessagesController::class,
                                        'action' => 'setup',
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    MessagesController::ROUTE_SETUP_DONE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/done',
                                            'defaults' => [
                                                'controller' => MessagesController::class,
                                                'action' => 'setupDone',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ]
        ]
    ],
];