<?php
use CG\Channel\Service as ChannelService;
use CG\Walmart\Account\CreationService as AccountCreator;
use Walmart\Controller\AccountController;
use Walmart\Module;
use Zend\Mvc\Router\Http\Literal;
use function CG\Stdlib\hyphenToClassname;

return [
    'SetupWizard' => [
        'SetupWizard' => [
            'white_listed_routes' => [
                Module::ROUTE.'/'.AccountController::ROUTE_SETUP => true,
                Module::ROUTE.'/'.AccountController::ROUTE_SETUP.'/'.AccountController::ROUTE_SAVE => true,
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [
            ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . hyphenToClassname(AccountCreator::CHANNEL) => dirname(__DIR__) . '/view/walmart/account/settings.phtml',
        ],
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/walmart',
                    'defaults' => [
                        'controller' => AccountController::class,
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    AccountController::ROUTE_SETUP => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/setup',
                            'defaults' => [
                                'action' => 'index',
                                'sidebar' => false,
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            AccountController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true
                            ],
                        ]
                    ],
                ]
            ]
        ],
    ],
    'di' => [
        'instance' => [
            AccountController::class => [
                'parameters' => [
                    'cryptor' => 'walmart_cryptor'
                ]
            ],
        ]
    ]
];
