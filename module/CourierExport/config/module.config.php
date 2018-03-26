<?php
use CourierExport\Controller\AccountController;
use Zend\Mvc\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            AccountController::ROUTE_SETUP => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/courierExport/:channel/setup[/:account]',
                    'defaults' => [
                        'controller' => AccountController::class,
                        'action' => 'setup',
                    ]
                ],
            ],
        ],
    ],
];