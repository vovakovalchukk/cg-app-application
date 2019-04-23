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
                        PermissionService::PARTNER_MANAGED_ROUTE_WHITELIST => true,
                    ],
                    'sidebar' => false,
                    'may_terminate' => true,
                    'child_routes' => []
                ],
            ]
        ],
    ],
    'di' => [
        'instance' => [
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'ekm_register_pending' => dirname(__DIR__) . '/view/register/pending.phtml',
            'ekm_register_failed' => dirname(__DIR__) . '/view/register/failed.phtml',
            'channel_ekm_powered_by_cg' => dirname(__DIR__) . '/view/powered_by_cg.phtml',
        ],
    ],
];