<?php
return [
    'router' => [
        'routes' => [
            'Orders' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/orders',
                    'defaults' => [
                        'controller' => 'Orders\Controller\Orders',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ajax' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '.json',
                            'defaults' => [
                                'action' => 'list',
                            ]
                        ]
                    ]
                ],
            ]
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Orders\Controller\Orders' => 'Orders\Controller\OrdersController',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];