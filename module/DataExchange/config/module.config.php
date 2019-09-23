<?php

use DataExchange\Controller\IndexController;
use DataExchange\Controller\StockImportController;
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
            'Stock' => [
                'label' => 'Stock',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    'Import' => [
                        'label' => 'Import',
                        'title' => 'Import',
                        'route' => Module::ROUTE . '/' . StockImportController::ROUTE
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
                    StockImportController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stock/import',
                            'defaults' => [
                                'controller' => StockImportController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => []
                    ],
                ]
            ]
        ],
    ],
];
