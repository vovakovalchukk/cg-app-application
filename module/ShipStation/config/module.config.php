<?php
use CG\Channel\Service as ChannelService;
use CG\ShipStation\Account as SSAccountService;
use ShipStation\Controller\AccountController;
use ShipStation\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [
            ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . 'ShipStation' => dirname(__DIR__) . '/view/ship-station/settings_account.phtml',
        ]
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/carrier-ss',
                    'defaults' => [],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    SSAccountService::ROUTE_SETUP => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/setup/:channel',
                            'defaults' => [
                                'controller' => AccountController::class,
                                'action' => 'setup',
                                'sidebar' => false,
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                    AccountController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/account',
                            'defaults' => [
                                'controller' => AccountController::class,
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            AccountController::ROUTE_SAVE => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ]
                    ]
                ],
            ],
        ]
    ],
    'di' => [
        'instance' => [
            AccountController::class => [
                'parameters' => [
                    'cryptor' => 'shipstation_cryptor',
                ]
            ]
        ]
    ]
];