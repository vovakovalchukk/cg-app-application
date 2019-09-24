<?php

use DataExchange\Controller\IndexController;
use DataExchange\Controller\StockExportController;
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
                    ],
                    'Export' => [
                        'label' => 'Export',
                        'title' => 'Export',
                        'route' => Module::ROUTE . '/' . StockExportController::ROUTE
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
                        'child_routes' => [
                            StockImportController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            StockImportController::ROUTE_REMOVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/remove',
                                    'defaults' => [
                                        'action' => 'remove'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ]
                    ],
                    StockExportController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stock/export',
                            'defaults' => [
                                'controller' => StockExportController::class,
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
