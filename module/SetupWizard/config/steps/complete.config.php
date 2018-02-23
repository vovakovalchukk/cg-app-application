<?php

use SetupWizard\Controller\CompleteController;
use SetupWizard\Module;
use Zend\Mvc\Router\Http\Literal;

return [
    'navigation' => [
        'setup-navigation' => [
            'Steps' => [
                'pages' => [
                    'complete' => [
                        'label' => 'Complete',
                        'title' => 'Complete',
                        'route' => Module::ROUTE . '/' . CompleteController::ROUTE_COMPLETE,
                        'order' => 999,
                        'sprite' => 'sprite-paper-plane-circle-25-white',
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
                    CompleteController::ROUTE_COMPLETE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/complete',
                            'defaults' => [
                                'controller' => CompleteController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            CompleteController::ROUTE_COMPLETE_THANKS => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/thanks',
                                    'defaults' => [
                                        'action' => 'thanks',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            CompleteController::ROUTE_COMPLETE_AJAX => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/ajax',
                                    'defaults' => [
                                        'action' => 'ajax',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ]
                ],
            ]
        ]
    ],
];