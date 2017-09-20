<?php

namespace Reports;

use Reports\Controller\SalesController;
use Reports\Controller\SalesJsonController;
use Zend\Mvc\Router\Http\Literal;

return [
    'navigation' => array(
        'application-navigation' => array(
            'reports' => array(
                'label'  => 'Sales',
                'sprite' => '',
                'order'  => 8,
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . SalesController::ROUTE_INDEX
            )
        )
    ),
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => SalesController::ROUTE_INDEX,
                    'defaults' => [
                        'controller' => SalesController::class,
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'sidebar' => Module::SIDEBAR_TEMPLATE
                    ]
                ],
                'child_routes' => [
                    'orderCounts' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/orderCounts',
                            'defaults' => [
                                'controller' => SalesJsonController::class,
                                'action' => 'orderCounts'
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                ],
                'may_terminate' => true
            ],
        ]
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy'
        ],
        'template_path_stack' => [
            __NAMESPACE__ => dirname(__DIR__) . '/view',
            PROJECT_ROOT . '/public' . Module::PUBLIC_FOLDER . 'template',
        ]
    ],
    'di' => [
        'instance' => [
            'aliases' => [
            ]
        ]
    ],
];
