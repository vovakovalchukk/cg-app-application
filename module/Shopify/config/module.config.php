<?php
use CG\Shopify\Account;
use CG_Shopify\Controller\AccountController;
use Zend\Mvc\Router\Http\Literal;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => []
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
                        ],
                    ],
                ],
            ],
        ],
    ],
];
