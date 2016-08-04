<?php
use BigCommerce\Controller\AccountController;
use BigCommerce\Controller\AppController;
use BigCommerce\Module;
use CG\BigCommerce\Account\CreationService as BigCommerceAccountCreator;
use CG\Channel\Service as ChannelService;
use Zend\Mvc\Router\Http\Literal;

return [
    'CG' => [
        'global' => [
            'white_listed_routes' => [
                implode('/', [Module::ROUTE, AppController::ROUTE_LOAD]) => true,
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [
            ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . \CG\Stdlib\hyphenToClassname(BigCommerceAccountCreator::CHANNEL) => dirname(__DIR__) . '/view/bigcommerce/account/settings.phtml',
        ],
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/bigcommerce',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    AccountController::ROUTE_AUTH => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/auth',
                            'defaults' => [
                                'controller' => AccountController::class,
                                'action' => 'authAndAuthRedirect',
                            ],
                        ],
                    ],
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
                    AppController::ROUTE_LOAD => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/load',
                            'defaults' => [
                                'controller' => AppController::class,
                                'action' => 'load',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
