<?php
use Products\Module;
use Products\Controller;
use Zend\Mvc\Router\Http\Literal;
use Products\Controller\ProductsJsonController;
use CG\Product\Service as ProductService;
use CG\Product\Storage\Api as ProductApiStorage;

return [
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/products',
                    'defaults' => [
                        'controller' => 'Products\Controller\Products',
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'sidebar' => 'products/products/sidebar'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
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
                'order'  => 5
            )
        )
    ),
];
