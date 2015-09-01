<?php
namespace Messages;

use Messages\Controller\HeadlineJsonController;
use Messages\Controller\IndexController;
use Messages\Controller\MessageJsonController;
use Messages\Controller\ThreadJsonController;
use Messages\Module;
use Messages\Thread\Service;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'navigation' => [
        'application-navigation' => [
            'messages' => [
                'label'  => 'Messages',
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . '/messages',
                'sprite' => 'sprite-messages-18-white',
                'order'  => 15,
                'pre-render' => [
                    'diLoad' => [
                        'class' => Service::class,
                        'method' => 'changeNavSpriteIfHasNew'
                    ]
                ],
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
                            ThreadJsonController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => ThreadJsonController::ROUTE_SAVE_URL,
                                    'defaults' => [
                                        'controller' => ThreadJsonController::class,
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            HeadlineJsonController::ROUTE_HEADLINE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => HeadlineJsonController::ROUTE_HEADLINE_URL,
                                    'defaults' => [
                                        'controller' => HeadlineJsonController::class,
                                        'action' => 'headline'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            MessageJsonController::ROUTE_ADD => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => MessageJsonController::ROUTE_ADD_URL,
                                    'defaults' => [
                                        'controller' => MessageJsonController::class,
                                        'action' => 'add'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    IndexController::ROUTE_THREAD => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/:threadId',
                            'defaults' => [
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
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