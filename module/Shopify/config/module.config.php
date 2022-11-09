<?php
use CG\Channel\Service as ChannelService;
use CG\Shopify\Account\CreationService as ShopifyAccountCreator;
use CG\Shopify\Account;
use Shopify\Controller\AccountController;
use Shopify\Controller\AppController;
use Shopify\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'CG' => [
        'global' => [
            'white_listed_routes' => [
                implode('/', [Module::ROUTE, AppController::ROUTE_OAUTH]) => true,
                implode('/', [Account::ROUTE_SHOPIFY, Account::ROUTE_SETUP, AccountController::ROUTE_SETUP_RETURN]) => true,
            ],
        ],
    ],
    'SetupWizard' => [
        'SetupWizard' => [
            'white_listed_routes' => [
                implode('/', [Account::ROUTE_SHOPIFY, Account::ROUTE_SETUP, AccountController::ROUTE_SETUP_LINK]) => true,
                implode('/', [Account::ROUTE_SHOPIFY, Account::ROUTE_SETUP, AccountController::ROUTE_SETUP_RETURN]) => true,
                implode('/', [Module::ROUTE, AppController::ROUTE_OAUTH]) => true,
            ]
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [
            ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . \CG\Stdlib\hyphenToClassname(ShopifyAccountCreator::CHANNEL) => dirname(__DIR__) . '/view/cg_shopify/account/settings.phtml',
        ],
    ],
    'router' => [
        'routes' => [
            Account::ROUTE_SHOPIFY => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/shopify',
                    'defaults' => [
                        'controller' => AccountController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    AppController::ROUTE_OAUTH => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/oauth',
                            'defaults' => [
                                'controller' => AppController::class,
                                'action' => 'oauth',
                            ],
                        ],
                    ],
                    Account::ROUTE_SETUP => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/setup',
                            'defaults' => [
                                'action' => 'setup',
                                'subHeader' => false,
                                'sidebar' => false,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            AccountController::ROUTE_SETUP_LINK => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/link',
                                    'defaults' => [
                                        'action' => 'link',
                                    ],
                                ],
                            ],
                            AccountController::ROUTE_SETUP_RETURN => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/return',
                                    'defaults' => [
                                        'action' => 'return',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
