<?php

use SetupWizard\Controller\RoyalMailController;
use SetupWizard\Module;
use Zend\Mvc\Router\Http\Literal;

return [
    'navigation' => [
        'setup-navigation' => [
            'Steps' => [
                'pages' => [
                    'royal-mail' => [
                        'label' => 'Add Royal Mail',
                        'title' => RoyalMailController::STEP_NAME,
                        'route' => Module::ROUTE . '/' . RoyalMailController::ROUTE_ROYAL_MAIL,
                        'order' => 30,
                        'sprite' => 'sprite-delivery-van-circle-25-white',
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
                    RoyalMailController::ROUTE_ROYAL_MAIL => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/royal-mail',
                            'defaults' => [
                                'controller' => RoyalMailController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                    ]
                ],
            ]
        ]
    ],
];