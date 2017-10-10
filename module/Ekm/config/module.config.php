<?php
use CG\Ekm\Registration\Storage\Api as EkmRegistrationApi;
use CG\Ekm\Registration\StorageInterface as EkmRegistrationStorage;
use Ekm\Controller\EkmRegistrationController;
use Zend\Mvc\Router\Http\Literal;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                EkmRegistrationStorage::class => EkmRegistrationApi::class,
            ],
            EkmRegistrationApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'ekm_register_pending' => dirname(__DIR__) . '/view/register/pending.phtml',
            'ekm_register_failed' => dirname(__DIR__) . '/view/register/failed.phtml',
            'channel_ekm_powered_by_cg' => dirname(__DIR__) . '/view/powered_by_cg.phtml',
        ],
    ],
    'router' => [
        'routes' => [
            EkmRegistrationController::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/ekm/register',
                    'defaults' => [
                        'controller' => EkmRegistrationController::class,
                        'action' => 'index',
                        'sidebar' => false,
                        'layout' => 'layout/login'
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    EkmRegistrationController::ROUTE_STATUS_CHECK => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/ajax',
                            'defaults' => [
                                'controller' => EkmRegistrationController::class,
                                'action' => 'checkStatus',
                            ],
                        ],
                    ]
                ]
            ],
        ],
    ],
    'CG' => [
        'global' => [
            'white_listed_routes' => [
                EkmRegistrationController::ROUTE . '/' . EkmRegistrationController::ROUTE_STATUS_CHECK => true
            ]
        ]
    ],
    'SetupWizard' => [
        'SetupWizard' => [
            'white_listed_routes' => [
                EkmRegistrationController::ROUTE . '/' . EkmRegistrationController::ROUTE_STATUS_CHECK => true,
            ],
        ],
    ],
];