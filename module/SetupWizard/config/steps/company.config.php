<?php

use SetupWizard\Controller\CompanyController;
use SetupWizard\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'navigation' => [
        'setup-navigation' => [
            'Steps' => [
                'pages' => [
                    'company' => [
                        'label' => 'Company Details',
                        'title' => 'Company Details',
                        'route' => Module::ROUTE . '/' . CompanyController::ROUTE_COMPANY,
                        'order' => 20,
                        'sprite' => 'sprite-pencil-circle-25-white',
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
                    CompanyController::ROUTE_COMPANY => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/company',
                            'defaults' => [
                                'controller' => CompanyController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            
                        ]
                    ]
                ],
            ]
        ]
    ],
];