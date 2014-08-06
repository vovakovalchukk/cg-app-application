<?php
use Products\Module;
use Zend\Mvc\Router\Http\Literal;
use Products\Controller\ProductsJsonController;
use CG\Product\Service as ProductService;
use CG\Product\Storage\Api as ProductApiStorage;
use Products\Controller\ProductsController;
use CG_UI\View\DataTable;
use CG_Mustache\View\Strategy as MustacheStrategy;

return [
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/products',
                    'defaults' => [
                        'controller' => ProductsController::class,
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
                    ]
                ]
            ]
        ]
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
            MustacheStrategy::class
        ],
        'template_path_stack' => [
            __NAMESPACE__ => dirname(__DIR__) . '/view',
            __NAMESPACE__ . '_mustache' => PROJECT_ROOT . '/public' . Module::PUBLIC_FOLDER . 'template',
        ]
    ],
    'di' => [
        'instance' => [
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
        ]
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
