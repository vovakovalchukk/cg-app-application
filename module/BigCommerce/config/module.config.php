<?php
use BigCommerce\Controller\AppController;
use BigCommerce\Module;
use Zend\Mvc\Router\Http\Literal;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/bigcommerce',
                    'defaults' => [
                        'controller' => AppController::class,
                        'action' => 'load',
                        'header' => false,
                        'subHeader' => false,
                        'sidebar' => false,
                        'breadcrumbs' => false
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    Module::ROUTE_OAUTH => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/oauth',
                            'defaults' => [
                                'action' => 'oauth',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
