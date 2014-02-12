<?php
use Orders\Controller;
use CG_UI\View\DataTable;
use Orders\Order\Service;
use CG\Order\Service\Alert\Service as AlertService;
use CG\Order\Client\Alert\Storage\Api as AlertApi;
use CG\Order\Service\Note\Service as NoteService;
use CG\Order\Client\Note\Storage\Api as NoteApi;
use CG\Order\Service\UserChange\Service as UserChangeService;
use CG\Order\Client\UserChange\Storage\Api as UserChangeApi;
use CG\Order\Service\Service as OrderService;
use CG\Order\Client\Storage\Api as OrderApi;
use Zend\View\Model\ViewModel;

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
                    'update-columns' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',

                        'options' => [
                            'route' => '/update-columns',
                            'defaults' => [
                                'action' => 'updateColumns'
                            ]
                        ],
                        'may_terminate' => true
                    ],
                    'order' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'priority' => -100,
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
                                    'set' => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/set',
                                            'defaults' => [
                                                'action' => 'set'
                                            ],
                                        ],
                                        'may_terminate' => true
                                    ],
                                    'delete' => [
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
                            'note' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/note',
                                    'defaults' => [
                                        'controller' => 'Orders\Controller\Note',
                                        'action' => 'index'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'create' => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/create',
                                            'defaults' => [
                                                'action' => 'create'
                                            ],
                                        ],
                                        'may_terminate' => true
                                    ],
                                    'update' => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/update',
                                            'defaults' => [
                                                'action' => 'update'
                                            ],
                                        ],
                                        'may_terminate' => true
                                    ],
                                    'delete' => [
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
                            'address' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/address',
                                    'defaults' => [
                                        'controller' => 'Orders\Controller\Address',
                                        'action' => 'update'
                                    ]
                                ],
                                'may_terminate' => true
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
                    'archive' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/archive.json',
                            'defaults' => [
                                'action' => 'archive',
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
            'Orders\Controller\Note' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\NoteController::class);
            },
            'Orders\Controller\Address' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\AddressController::class);
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
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'di' => [
        'instance' => [
            'aliases' => [
                'OrdersTable' => DataTable::class,
                'OrdersCheckboxColumnView' => ViewModel::class,
                'OrdersCheckboxColumn' => DataTable\Column::class,
                'OrdersChannelColumnView' => ViewModel::class,
                'OrdersChannelColumn' => DataTable\Column::class,
                'OrdersAccountColumnView' => ViewModel::class,
                'OrdersAccountColumn' => DataTable\Column::class,
                'OrdersDateColumnView' => ViewModel::class,
                'OrdersDateColumn' => DataTable\Column::class,
                'OrdersIdColumnView' => ViewModel::class,
                'OrdersIdColumn' => DataTable\Column::class,
                'OrdersTotalColumnView' => ViewModel::class,
                'OrdersTotalColumn' => DataTable\Column::class,
                'OrdersBuyerColumnView' => ViewModel::class,
                'OrdersBuyerColumn' => DataTable\Column::class,
                'OrdersStatusColumnView' => ViewModel::class,
                'OrdersStatusColumn' => DataTable\Column::class,
                'OrdersBatchColumnView' => ViewModel::class,
                'OrdersBatchColumn' => DataTable\Column::class,
                'OrdersMessagesColumnView' => ViewModel::class,
                'OrdersMessagesColumn' => DataTable\Column::class,
                'OrdersShippingColumnView' => ViewModel::class,
                'OrdersShippingColumn' => DataTable\Column::class,
                'OrdersDispatchColumnView' => ViewModel::class,
                'OrdersDispatchColumn' => DataTable\Column::class,
                'OrdersPrintColumnView' => ViewModel::class,
                'OrdersPrintColumn' => DataTable\Column::class,
                'OrdersTagColumnView' => ViewModel::class,
                'OrdersTagColumn' => DataTable\Column::class,
                'OrdersOptionsColumnView' => ViewModel::class,
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
            'OrdersCheckboxColumnView' => [
                'parameters' => [
                    'template' => 'orders/orders/table/header/checkbox.phtml',
                ],
            ],
            'OrdersCheckboxColumn' => [
                'parameters' => [
                    'column' => 'id',
                    'viewModel' => 'OrdersCheckboxColumnView',
                    'class' => 'checkbox',
                    'sortable' => false
                ],
            ],
            'OrdersChannelColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Channel'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersChannelColumn' => [
                'parameters' => [
                    'column' => 'channel',
                    'viewModel' => 'OrdersChannelColumnView',
                    'width' => 70,
                ],
            ],
            'OrdersAccountColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Account'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersAccountColumn' => [
                'parameters' => [
                    'column' => 'accountId',
                    'viewModel' => 'OrdersAccountColumnView',
                    'width' => 50,
                ],
            ],
            'OrdersDateColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Order Date'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersDateColumn' => [
                'parameters' => [
                    'column' => 'purchaseDate',
                    'viewModel' => 'OrdersDateColumnView',
                    'width' => 80,
                ],
            ],
            'OrdersIdColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Order ID / Product Information'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersIdColumn' => [
                'parameters' => [
                    'column' => 'externalId',
                    'viewModel' => 'OrdersIdColumnView',
                ],
            ],
            'OrdersTotalColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Total'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersTotalColumn' => [
                'parameters' => [
                    'column' => 'total',
                    'viewModel' => 'OrdersTotalColumnView',
                    'width' => 50,
                ],
            ],
            'OrdersBuyerColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Buyer'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersBuyerColumn' => [
                'parameters' => [
                    'column' => 'billingAddressFullName',
                    'viewModel' => 'OrdersBuyerColumnView',
                    'width' => 100,
                ],
            ],
            'OrdersStatusColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Status'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersStatusColumn' => [
                'parameters' => [
                    'column' => 'status',
                    'viewModel' => 'OrdersStatusColumnView',
                    'class' => 'status-col',
                ],
            ],
            'OrdersBatchColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Batch'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersBatchColumn' => [
                'parameters' => [
                    'column' => 'batch',
                    'viewModel' => 'OrdersBatchColumnView',
                    'width' => 50,
                ],
            ],
            'OrdersMessagesColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Messages'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersMessagesColumn' => [
                'parameters' => [
                    'column' => 'buyerMessage',
                    'viewModel' => 'OrdersMessagesColumnView',
                    'width' => 50,
                ],
            ],
            'OrdersShippingColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Shipping Method'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersShippingColumn' => [
                'parameters' => [
                    'column' => 'shippingMethod',
                    'viewModel' => 'OrdersShippingColumnView',
                    'width' => '100',
                ],
            ],
            'OrdersDispatchColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Dispatch'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersDispatchColumn' => [
                'parameters' => [
                    'column' => 'dispatchDate',
                    'viewModel' => 'OrdersDispatchColumnView',
                    'class' => 'actions',
                ],
            ],
            'OrdersPrintColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Print'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersPrintColumn' => [
                'parameters' => [
                    'column' => 'printedDate',
                    'viewModel' => 'OrdersPrintColumnView',
                    'class' => 'actions',
                ],
            ],
            'OrdersTagColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Tag'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersTagColumn' => [
                'parameters' => [
                    'column' => 'tag',
                    'viewModel' => 'OrdersTagColumnView'
                ]
            ],
            'OrdersOptionsColumnView' => [
                'parameters' => [
                    'template' => 'table/column-picker.phtml',
                ],
            ],
            'OrdersOptionsColumn' => [
                'parameters' => [
                    'viewModel' => 'OrdersOptionsColumnView',
                    'class' => 'options',
                    'defaultContent' => '',
                    'sortable' => false
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
            ],
            NoteService::class => [
                'parameters' => [
                    'repository' => NoteApi::class
                ]
            ],
            NoteApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            UserChangeService::class => [
                'parameters' => [
                    'repository' => UserChangeApi::class
                ]
            ],
            UserChangeApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            OrderApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ]
        ],
    ],
];
