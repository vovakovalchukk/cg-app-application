<?php
namespace Messages;

use Messages\Controller\HeadlineJsonController;
use Messages\Controller\IndexController;
use Messages\Controller\MessageJsonController;
use Messages\Controller\MessageTemplateJsonController;
use Messages\Controller\ThreadJsonController;
use Messages\Module;
use Messages\Thread\Service;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Regex;
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
                        'priority' => -50,
                        'options' => [
                            'route' => '/:threadId',
                            'defaults' => [
                                'action' => 'index',
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
                                    ThreadJsonController::ROUTE_COUNTS => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => ThreadJsonController::ROUTE_COUNTS_URL,
                                            'defaults' => [
                                                'action' => 'counts',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    MessageTemplateJsonController::ROUTE_TEMPLATES => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/templates',
                            'defaults' => [
                                'controller' => MessageTemplateJsonController::class,
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            MessageTemplateJsonController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    // The Messages tab is now a single-page app managed by React Router which requires this default route
                    'Default Route' => [
                        'type' => Regex::class,
                        'priority' => -100,
                        'options' => [
                            'regex' => '\/(?<route>[\/A-Za-z0-9]{2,})',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action' => 'index',
                                'breadcrumbs' => false,
                                'sidebar' => false
                            ],
                            'spec' => '/%route%'
                        ],
                        'may_terminate' => true
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
