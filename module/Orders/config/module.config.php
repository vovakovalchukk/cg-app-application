<?php
use Orders\Controller;
use CG_UI\View\DataTable;
use Orders\Order\Service;
use CG\Order\Shared\Alert\StorageInterface;
use CG\Order\Service\Alert\Service as AlertService;
use CG\Order\Client\Alert\Storage\Api as AlertApi;

return [
    'router' => [
        'routes' => [
            'Orders' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/orders',
                    'defaults' => [
                        'controller' => 'Orders\Controller\Orders',
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'sidebar' => 'orders/orders/sidebar'
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
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'alert' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/alert',
                                    'defaults' => [
                                        'controller' => 'Orders\Controller\Alert'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'alertSet' => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/set',
                                            'defaults' => [
                                                'action' => 'set'
                                            ],
                                        ],
                                        'may_terminate' => true
                                    ],
                                    'alertDelete' => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/delete',
                                            'defaults' => [
                                                'action' => 'delete'
                                            ]
                                        ],
                                        'may_terminate' => true
                                    ],
                                ]
                            ],
                        ]
                    ],
                    'tag' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/tag.json',
                            'defaults' => [
                                'action' => 'tag',
                            ]
                        ]
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'Orders\Controller\Orders' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\OrdersController::class);
            },
            'Orders\Controller\Alert' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\AlertController::class);
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
                'OrdersTagColumn' => DataTable\Column::class,
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
                    'OrdersTagColumn',
                    'OrdersOptionsColumn',
                ],
            ],
            'OrdersCheckboxColumn' => [
                'parameters' => [
                    'column' => 'id',
                    'html' => '<input type="checkbox" name="select-all" class="select-all" data-group="mainTable" />',
                    'class' => 'checkbox',
                    'sortable' => false
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
            'OrdersTagColumn' => [
                'parameters' => [
                    'column' => 'tag',
                    'html' => 'Tag'
                ]
            ],
            'OrdersOptionsColumn' => [
                'parameters' => [
                    'html' => '<span class="icon-med cog">Options</span>',
                    'class' => 'options',
                    'defaultContent' => '',
                ],
            ],
            AlertService::class => [
                'parameters' => [
                    'repository' => AlertApi::class
                ]
            ],
            AlertApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ]
        ],
    ],
];