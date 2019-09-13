<?php

use DataExchange\Controller\IndexController;
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
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . '/dataExchange'
            ]
        ],
        'data-exchange-navigation' => [
            // Example container with pages
            'Example' => [
                'label' => 'Example',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    'Example' => [
                        'label' => 'Example',
                        'title' => 'Example',
                        'route' => Module::ROUTE . '/' . IndexController::ROUTE_EXAMPLE
                    ]
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
                    IndexController::ROUTE_EXAMPLE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/example',
                            'defaults' => [
                                'action' => 'example'
                            ]
                        ],
                    ]
                ]
            ]
        ],
    ],
];
