<?php

use SetupWizard\Controller\IndexController;
use SetupWizard\Module;
use Zend\Mvc\Router\Http\Literal;

return [
    'navigation' => [
        'setup-navigation' => [
            'Steps' => [
                'pages' => [
                    'example' => [
                        'label' => 'Example',
                        'title' => 'Example',
                        'route' => Module::ROUTE . '/' . IndexController::ROUTE_EXAMPLE,
                        'order' => 1,
                        'sprite' => 'sprite-settings-24-white',
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
                    IndexController::ROUTE_EXAMPLE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/example',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action' => 'example',
                            ]
                        ],
                        'may_terminate' => true,
                    ]
                ],
            ]
        ]
    ],
];