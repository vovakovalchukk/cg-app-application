<?php

use Redux\Controller\IndexController;
use Redux\Module;
use Zend\Mvc\Router\Http\Literal;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [

        ]
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/redux',
                    'defaults' => [],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    IndexController::ROUTE_INDEX=> [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/prototype',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action' => 'index',
                                'breadcrumbs' => false,
                                'sidebar' => false,
                                'subHeader' => false,
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ]
    ]
];