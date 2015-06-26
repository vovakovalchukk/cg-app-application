<?php
namespace Messages;

use Messages\Controller\IndexController;
use Messages\Controller\ThreadJsonController;
use Messages\Module;
use Zend\Mvc\Router\Http\Literal;

return [
    'navigation' => [
        'application-navigation' => [
            'messages' => [
                'label'  => 'Messages',
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . '/messages',
                'sprite' => 'sprite-messages-18-white',
                'order'  => 15,
            ]
        ]
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => IndexController::ROUTE_INDEX_URL,
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'sidebar' => false
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    ThreadJsonController::ROUTE_AJAX => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => ThreadJsonController::ROUTE_AJAX_URL,
                            'defaults' => [
                                'controller' => ThreadJsonController::class,
                                'action' => 'ajax'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            ThreadJsonController::ROUTE_THREAD => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => ThreadJsonController::ROUTE_THREAD_URL,
                                    'defaults' => [
                                        'controller' => ThreadJsonController::class,
                                        'action' => 'thread'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                ],
            ]
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