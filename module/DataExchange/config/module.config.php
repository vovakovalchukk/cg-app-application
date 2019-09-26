<?php

use CG\DataExchangeTemplate\Entity as DataExchangeTemplate;
use DataExchange\Controller\IndexController;
use DataExchange\Controller\TemplateController;
use DataExchange\Module;
use DataExchange\Navigation\Factory as DataExchangeNavigation;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
    ],
    'navigation' => [
        'application-navigation' => [
            'data-exchange' => [
                'label'  => 'Data Exchange',
                'sprite' => 'sprite-exchange-white-18',
                'order'  => 17,
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . '/dataExchange',
                'feature-flag' => Module::FEATURE_FLAG,
            ]
        ],
        'data-exchange-navigation' => [
            'Stock' => [
                'label' => 'Stock',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    'Templates' => [
                        'label' => 'Templates',
                        'title' => 'Templates',
                        'route' => Module::ROUTE . '/' . TemplateController::ROUTE,
                        'params' => [
                            'type' => TemplateController::getRouteTypeForTemplateType(DataExchangeTemplate::TYPE_STOCK)
                        ]
                    ]
                ]
            ],
            'Orders' => [
                'label' => 'Orders',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    'Templates' => [
                        'label' => 'Templates',
                        'title' => 'Templates',
                        'route' => Module::ROUTE . '/' . TemplateController::ROUTE,
                        'params' => [
                            'type' => TemplateController::getRouteTypeForTemplateType(DataExchangeTemplate::TYPE_ORDER)
                        ]
                    ]
                ]
            ],
        ]
    ],
    'service_manager' => [
        'factories' => [
            'data-exchange-navigation'  => DataExchangeNavigation::class,
        ]
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/dataExchange',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'sub-header' => false,
                        'sidebar' => Module::TEMPLATE_SIDEBAR
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    TemplateController::ROUTE => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:type/templates',
                            'constraints' => [
                                'type' => implode('|', TemplateController::getAllowedRouteTypes())
                            ],
                            'defaults' => [
                                'controller' => TemplateController::class,
                                'action' => 'index',

                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            TemplateController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'controller' => TemplateController::class,
                                        'action' => 'save'
                                    ]
                                ]
                            ],
                            TemplateController::ROUTE_REMOVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/remove',
                                    'defaults' => [
                                        'controller' => TemplateController::class,
                                        'action' => 'remove'
                                    ]
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ],
    ],
];
