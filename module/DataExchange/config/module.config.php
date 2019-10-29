<?php

use CG\DataExchangeTemplate\Entity as DataExchangeTemplate;
use DataExchange\Controller\EmailAccountController;
use DataExchange\Controller\FtpAccountController;
use DataExchange\Controller\HistoryController;
use DataExchange\Controller\IndexController;
use DataExchange\Controller\OrderExportController;
use DataExchange\Controller\StockExportController;
use DataExchange\Controller\StockImportController;
use DataExchange\Controller\TemplateController;
use DataExchange\History\Service as HistoryService;
use DataExchange\Module;
use DataExchange\Navigation\Factory as DataExchangeNavigation;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

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
                    ],
                    'Templates' => [
                        'label' => 'Templates',
                        'title' => 'Templates',
                        'route' => Module::ROUTE . '/' . TemplateController::ROUTE,
                        'params' => [
                            'type' => TemplateController::getRouteTypeForTemplateType(DataExchangeTemplate::TYPE_STOCK)
                        ]
                    ]
                ]
            ],
            'Orders' => [
                'label' => 'Orders',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    'Export' => [
                        'label' => 'Export',
                        'title' => 'Export',
                        'route' => Module::ROUTE . '/' . OrderExportController::ROUTE
                    ],
                    'Templates' => [
                        'label' => 'Templates',
                        'title' => 'Templates',
                        'route' => Module::ROUTE . '/' . TemplateController::ROUTE,
                        'params' => [
                            'type' => TemplateController::getRouteTypeForTemplateType(DataExchangeTemplate::TYPE_ORDER)
                        ]
                    ]
                ]
            ],
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
            'History' => [
                'label' => 'History',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    'History' => [
                        'label' => 'History',
                        'title' => 'History',
                        'route' => Module::ROUTE . '/' . HistoryController::ROUTE
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
                            EmailAccountController::ROUTE_REMOVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/remove',
                                    'defaults' => [
                                        'action' => 'remove'
                                    ]
                                ],
                            ],
                            EmailAccountController::ROUTE_VERIFY => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/verify',
                                    'defaults' => [
                                        'action' => 'verify'
                                    ]
                                ],
                            ],
                        ]
                    ],
                    TemplateController::ROUTE => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:type/templates',
                            'constraints' => [
                                'type' => implode('|', TemplateController::getAllowedRouteTypes())
                            ],
                            'defaults' => [
                                'controller' => TemplateController::class,
                                'action' => 'index',

                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            TemplateController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'controller' => TemplateController::class,
                                        'action' => 'save'
                                    ]
                                ]
                            ],
                            TemplateController::ROUTE_REMOVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/remove',
                                    'defaults' => [
                                        'controller' => TemplateController::class,
                                        'action' => 'remove'
                                    ]
                                ]
                            ],
                        ]
                    ],
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
                        'child_routes' => [
                            StockExportController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            StockExportController::ROUTE_REMOVE => [
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
                    OrderExportController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/order/export',
                            'defaults' => [
                                'controller' => OrderExportController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            OrderExportController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            OrderExportController::ROUTE_REMOVE => [
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
                    HistoryController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/history',
                            'defaults' => [
                                'controller' => HistoryController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            HistoryController::ROUTE_FETCH => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/fetch',
                                    'defaults' => [
                                        'action' => 'fetch'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            HistoryController::ROUTE_FILES => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/files/:historyId/:fileType',
                                    'constraints' => [
                                        'historyId' => '[0-9]+',
                                        'fileType' => implode('|', HistoryService::getAllowedFileTypes())
                                    ],
                                    'defaults' => [
                                        'action' => 'files'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            HistoryController::ROUTE_STOP => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/stop',
                                    'defaults' => [
                                        'action' => 'stop'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ]
                    ],
                ]
            ]
        ],
    ],
];
