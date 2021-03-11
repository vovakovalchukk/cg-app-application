<?php
use SetupWizard\Controller\DemoController;
use SetupWizard\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'navigation' => [
        'setup-navigation' => [
            'Steps' => [
                'pages' => [
                    'demo' => [
                        'label' => 'Demonstration',
                        'title' => 'Demonstration',
                        'route' => Module::ROUTE . '/' . DemoController::ROUTE_DEMO,
                        'order' => 15,
                        'sprite' => 'sprite-card-circle-25-white',
                        'link' => false,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'child_routes' => [
                    DemoController::ROUTE_DEMO => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/demonstration',
                            'defaults' => [
                                'controller' => DemoController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [

                        ],
                    ],
                ],
            ],
        ],
    ],
];