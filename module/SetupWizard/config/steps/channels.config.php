<?php

use SetupWizard\Controller\ChannelsController;
use SetupWizard\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'navigation' => [
        'setup-navigation' => [
            'Steps' => [
                'pages' => [
                    'channels' => [
                        'label' => 'Add Channels',
                        'title' => 'Add Channels',
                        'route' => Module::ROUTE . '/' . ChannelsController::ROUTE_CHANNELS,
                        'order' => 10,
                        'sprite' => 'sprite-channels-circle-25-white',
                        'link' => false,
                    ],
                ]
            ],
        ],
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'child_routes' => [
                    ChannelsController::ROUTE_CHANNELS => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/channels',
                            'defaults' => [
                                'controller' => ChannelsController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            ChannelsController::ROUTE_CHANNEL_PICK => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/pick',
                                    'defaults' => [
                                        'action' => 'pick',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            ChannelsController::ROUTE_CHANNEL_ADD => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/add',
                                    'defaults' => [
                                        'action' => 'add',
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    ChannelsController::ROUTE_CHANNEL_CONNECT => [
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/:channel[/:region]',
                                            'defaults' => [
                                                'action' => 'connect',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                ]
                            ],
                            ChannelsController::ROUTE_CHANNEL_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            ChannelsController::ROUTE_CHANNEL_DELETE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'action' => 'delete',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ]
                    ]
                ],
            ]
        ]
    ],
];