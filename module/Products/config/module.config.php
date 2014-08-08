<?php
use Products\Module;
use Products\Controller;
use CG\Http\Rpc\Json\Client as JsonRpcClient;
use Orders\Order\Invoice\Renderer\ServiceInterface as InvoiceRendererService;
use Orders\Order\Invoice\Renderer\Service\Pdf as PdfInvoiceRendererService;
use Orders\Controller\StoredFiltersController;
use CG\Order\Service\Filter\StorageInterface as FilterStorageInterface;
use CG\Order\Client\Filter\Storage\Api as FilterStorage;
use Orders\Controller\BulkActionsController;
use Zend\Mvc\Router\Http\Literal;
use Products\Controller\ProductsJsonController;
use CG\Product\Service as ProductService;
use CG\Product\Storage\Api as ProductApiStorage;


return [
    'router' => [
        'routes' => [
            'Products' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/products',
                    'defaults' => [
                        'controller' => 'Products\Controller\Products',
                        'action' => 'index',
                        'breadcrumbs' => false,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ajax' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/ajax',
                            'defaults' => [
                                'action' => 'jsonFilter',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filterId' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:filterId',
                                    'constraints' => [
                                        'filterId' => '.+'
                                    ],
                                    'defaults' => [
                                        'action' => 'jsonFilterId',
                                    ]
                                ],
                            ],
                        ],
                    ],
                    ProductsJsonController::AJAX_ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/ajax',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'ajax'
                            ]
                        ],
                    ],
                    'archive' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/archive',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'archiveOrderIds',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filterId' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:filterId',
                                    'constraints' => [
                                        'filterId' => '.+'
                                    ],
                                    'defaults' => [
                                        'action' => 'archiveFilterId',
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'invoice' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/invoice',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'invoiceOrderIds'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filterId' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:filterId',
                                    'constraints' => [
                                        'filterId' => '.+'
                                    ],
                                    'defaults' => [
                                        'action' => 'invoiceFilterId',
                                    ]
                                ],
                            ],
                            'invoice_demo' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/preview',
                                    'defaults' => [
                                        'action' => 'previewInvoice'
                                    ]
                                ],
                            ],
                            'invoice_check' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/check',
                                    'defaults' => [
                                        'action' => 'checkInvoicePrintingAllowed'
                                    ]
                                ],
                            ],
                        ]
                    ],
                    StoredFiltersController::ROUTE_SAVE => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/filter/save',
                            'defaults' => [
                                'controller' => StoredFiltersController::class,
                                'action' => 'saveFilter'
                            ]
                        ]
                    ],
                    StoredFiltersController::ROUTE_REMOVE => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/filter/remove',
                            'defaults' => [
                                'controller' => StoredFiltersController::class,
                                'action' => 'removeFilter'
                            ]
                        ]
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'Products\Controller\Products' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\ProductsController::class);
            },
        ],
        'invokables' => [],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
            PROJECT_ROOT . '/public' . Module::PUBLIC_FOLDER . 'template',
        ],
        'strategies' => [
            'ViewJsonStrategy',
            'CG_Mustache\View\Strategy'
        ],
    ],
    'di' => [
        'instance' => [
            'aliases' => [
                'OrderRpcClient' => JsonRpcClient::class,
            ],
            'preferences' => [
                InvoiceRendererService::class => PdfInvoiceRendererService::class,
                FilterStorageInterface::class => FilterStorage::class,
            ],
            ProductService::class => [
                'parameters' => [
                    'repository' => ProductApiStorage::class
                ]
            ],
            ProductApiStorage::class => [
               'parameters' => [
                   'client' => 'cg_app_guzzle'
                ]
            ]
        ],
    ],
    'navigation' => array(
        'application-navigation' => array(
            'products' => array(
                'label'  => 'Products',
                'route'  => 'Products',
                'sprite' => 'sprite-orders-18-white',
                'product'  => 10
            )
        )
    ),
];
