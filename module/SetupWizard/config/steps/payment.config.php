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
                            PaymentController::ROUTE_PACKAGE_REMEMBER => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/rememberPackage/:id',
                                    'defaults' => [
                                        'action' => 'rememberPackage',
                                    ]
                                ],
                            ],
                            PaymentController::ROUTE_PACKAGE_SET => [
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