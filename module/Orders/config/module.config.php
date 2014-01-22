<?php
use Orders\Controller;
use CG_UI\View\DataTable;
use Orders\Order\Service;

return [
    'router' => [
        'routes' => [
            'Orders' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/orders',
                    'defaults' => [
                        'controller' => 'Orders\Controller\Orders',
                        'action' => 'index'
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ajax' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '.json',
                            'defaults' => [
                                'action' => 'json',
                            ]
                        ]
                    ],
                    'batch' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/batch'
                        ),
                        'may_terminate' => true, //This should be false but seems to be broken
                        'child_routes' => array(
                            'create' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                    'route'    => '/create',
                                    'defaults' => array(
                                        'controller' => 'Orders\Controller\Batch',
                                        'action'     => 'create',
                                    ),
                                ),
                                'may_terminate' => true
                            ),
                            'delete' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                    'route'    => '/delete',
                                    'defaults' => array(
                                        'controller' => 'Orders\Controller\Batch',
                                        'action'     => 'delete',
                                    ),
                                ),
                                'may_terminate' => true
                            )
                        )
                    ]
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'Orders\Controller\Orders' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\OrdersController::class);
            },
            'Orders\Controller\Batch' => function($controllerManager) {
                    return $controllerManager->getServiceLocator()->get(Controller\BatchController::class);
            }
        ],
        'invokables' => [],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
            'Mustache\View\Strategy'
        ],
    ],
    'di' => [
        'instance' => [
            'aliases' => [
                'OrdersTable' => DataTable::class,
                'OrdersCheckboxColumn' => DataTable\Column::class,
                'OrdersChannelColumn' => DataTable\Column::class,
                'OrdersAccountColumn' => DataTable\Column::class,
                'OrdersDateColumn' => DataTable\Column::class,
                'OrdersIdColumn' => DataTable\Column::class,
                'OrdersTotalColumn' => DataTable\Column::class,
                'OrdersBuyerColumn' => DataTable\Column::class,
                'OrdersStatusColumn' => DataTable\Column::class,
                'OrdersBatchColumn' => DataTable\Column::class,
                'OrdersMessagesColumn' => DataTable\Column::class,
                'OrdersShippingColumn' => DataTable\Column::class,
                'OrdersDispatchColumn' => DataTable\Column::class,
                'OrdersPrintColumn' => DataTable\Column::class,
                'OrdersOptionsColumn' => DataTable\Column::class,
            ],
            Service::class => [
                'parameters' => [
                    'ordersTable' => 'OrdersTable',
                ],
            ],
            'OrdersTable' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'datatable',
                        'class' => 'fixed-header fixed-footer',
                        'width' => '100%',
                    ],
                ],
                'injections' => [
                    'OrdersCheckboxColumn',
                    'OrdersChannelColumn',
                    'OrdersAccountColumn',
                    'OrdersDateColumn',
                    'OrdersIdColumn',
                    'OrdersTotalColumn',
                    'OrdersBuyerColumn',
                    'OrdersStatusColumn',
                    'OrdersBatchColumn',
                    'OrdersMessagesColumn',
                    'OrdersShippingColumn',
                    'OrdersDispatchColumn',
                    'OrdersPrintColumn',
                    'OrdersOptionsColumn',
                ],
            ],
            'OrdersCheckboxColumn' => [
                'parameters' => [
                    'column' => 'id',
                    'html' => '<input type="checkbox" name="select-all" class="select-all" data-group="mainTable" />',
                    'class' => 'checkbox',
                ],
            ],
            'OrdersChannelColumn' => [
                'parameters' => [
                    'column' => 'channel',
                    'html' => 'Channel',
                    'width' => 70,
                ],
            ],
            'OrdersAccountColumn' => [
                'parameters' => [
                    'column' => 'accountId',
                    'html' => 'Account',
                    'width' => 50,
                ],
            ],
            'OrdersDateColumn' => [
                'parameters' => [
                    'column' => 'purchaseDate',
                    'html' => 'Order Date',
                    'width' => 80,
                ],
            ],
            'OrdersIdColumn' => [
                'parameters' => [
                    'column' => 'externalId',
                    'html' => 'Order ID / Product Information',
                ],
            ],
            'OrdersTotalColumn' => [
                'parameters' => [
                    'column' => 'total',
                    'html' => 'Total',
                    'width' => 50,
                ],
            ],
            'OrdersBuyerColumn' => [
                'parameters' => [
                    'column' => 'billingAddressFullName',
                    'html' => 'Buyer',
                    'width' => 100,
                ],
            ],
            'OrdersStatusColumn' => [
                'parameters' => [
                    'column' => 'status',
                    'html' => 'Status',
                    'class' => 'status-col',
                ],
            ],
            'OrdersBatchColumn' => [
                'parameters' => [
                    'column' => 'batch',
                    'html' => 'Batch',
                    'width' => 50,
                ],
            ],
            'OrdersMessagesColumn' => [
                'parameters' => [
                    'column' => 'buyerMessage',
                    'html' => 'Messages',
                    'width' => 50,
                ],
            ],
            'OrdersShippingColumn' => [
                'parameters' => [
                    'column' => 'shippingMethod',
                    'html' => 'Shipping Method',
                    'width' => '100',
                ],
            ],
            'OrdersDispatchColumn' => [
                'parameters' => [
                    'column' => 'dispatchDate',
                    'html' => 'Dispatch',
                    'class' => 'actions',
                ],
            ],
            'OrdersPrintColumn' => [
                'parameters' => [
                    'column' => 'printedDate',
                    'html' => 'Print',
                    'class' => 'actions',
                ],
            ],
            'OrdersOptionsColumn' => [
                'parameters' => [
                    'html' => '<span class="icon-med cog">Options</span>',
                    'class' => 'options',
                    'defaultContent' => '',
                ],
            ],
        ],
    ],
];