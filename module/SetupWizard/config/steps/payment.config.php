<?php
use SetupWizard\Controller\PaymentController;
use SetupWizard\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'navigation' => [
        'setup-navigation' => [
            'Steps' => [
                'pages' => [
                    'messages' => [
                        'label' => 'Add Payment Method',
                        'title' => 'Add Payment Method',
                        'route' => Module::ROUTE . '/' . PaymentController::ROUTE_PAYMENT,
                        'order' => 20,
                        'sprite' => 'sprite-cash-circle-25-white',
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
                    PaymentController::ROUTE_PAYMENT => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/payment',
                            'defaults' => [
                                'controller' => PaymentController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            PaymentController::ROUTE_PACKAGE => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/setPackage/:id',
                                    'defaults' => [
                                        'action' => 'setPackage',
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];