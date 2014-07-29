<?php
use Products\Module;
use Zend\Mvc\Router\Http\Literal;
use Products\Controller\ProductsJsonController;

return [
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/products'
                ],
                'may_terminate' => false, //=true when you add products list page
                'child_routes' => [
                    ProductsJsonController::AJAX_ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/ajax',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'ajax'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
