<?php

use CG_Permission\Service as PermissionService;
use Partner\Controller\AccountController;
use Zend\Mvc\Router\Http\Literal;

return [
    'router' => [
        'routes' => [
            AccountController::ROUTE_AUTHORISE_ACCOUNT => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/authoriseAccount',
                    'defaults' => [
                        'controller' => AccountController::class,
                        'action' => 'index',
                        'sidebar' => false,
                        'layout' => 'partner/account/layout',
                        PermissionService::PARTNER_MANAGED_ROUTE_WHITELIST => true,
                    ],
                    'sidebar' => false,
                    'may_terminate' => true,
                    'child_routes' => []
                ],
            ]
        ],
    ],
    'CG' => [
        'global' => [
            'white_listed_routes' => [
                AccountController::ROUTE_AUTHORISE_ACCOUNT => true
            ]
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __NAMESPACE__ => dirname(__DIR__) . '/view'
        ]
    ],
];