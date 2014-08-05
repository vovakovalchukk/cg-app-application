<?php
use Products\Module;
use Products\Controller;
use CG_UI\View\DataTable;

return [
    'router' => [
        'routes' => [
            'Products' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
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
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'Products\Controller\Products' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\ProductsController::class);
            }
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
