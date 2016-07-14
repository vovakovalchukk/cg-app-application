<?php
use BigCommerce\Controller\AccountController;
use BigCommerce\Controller\AppController;
use BigCommerce\Module;
use CG\BigCommerce\Account\CreationService as BigCommerceAccountCreator;
use CG\Channel\Service as ChannelService;
use Zend\Mvc\Router\Http\Literal;

return [
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
                    'defaults' => [
                        'controller' => AppController::class,
                        'action' => 'load',
                        'header' => false,
                        'subHeader' => false,
                        'sidebar' => false,
                        'breadcrumbs' => false
                    ],
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
                                'action' => 'oauth',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
