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
                    'order' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route' => '/:order',
                            'defaults' => [
                                'action' => 'order',
                            ]
                        ]
                    ]
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'Orders\Controller\Orders' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\OrdersController::Class);
            },
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
                'OrdersTable' => DataTable::Class,
                'OrdersCheckboxColumn' => DataTable\Column::Class,
                'OrdersChannelColumn' => DataTable\Column::Class,
                'OrdersAccountColumn' => DataTable\Column::Class,
                'OrdersDateColumn' => DataTable\Column::Class,
                'OrdersIdColumn' => DataTable\Column::Class,
                'OrdersTotalColumn' => DataTable\Column::Class,
                'OrdersBuyerColumn' => DataTable\Column::Class,
                'OrdersStatusColumn' => DataTable\Column::Class,
                'OrdersBatchColumn' => DataTable\Column::Class,
                'OrdersMessagesColumn' => DataTable\Column::Class,
                'OrdersShippingColumn' => DataTable\Column::Class,
                'OrdersDispatchColumn' => DataTable\Column::Class,
                'OrdersPrintColumn' => DataTable\Column::Class,
                'OrdersOptionsColumn' => DataTable\Column::Class,
            ],
            Service::Class => [
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