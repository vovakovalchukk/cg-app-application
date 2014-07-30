<?php
use Orders\Module;
use Orders\Controller;
use CG_UI\View\DataTable;
use Orders\Order\TableService;
use CG\Order\Service\Alert\Service as AlertService;
use CG\Order\Client\Alert\Storage\Api as AlertApi;
use CG\Order\Service\Note\Service as NoteService;
use CG\Order\Client\Note\Storage\Api as NoteApi;
use CG\Order\Service\UserChange\Service as UserChangeService;
use CG\Order\Client\UserChange\Storage\Api as UserChangeApi;
use CG\Order\Client\Storage\Api as OrderApi;
use Zend\View\Model\ViewModel;
use Orders\Order\Service as OrderService;
use CG\Http\Rpc\Json\Client as JsonRpcClient;
use Orders\Order\Invoice\Renderer\ServiceInterface as InvoiceRendererService;
use Orders\Order\Invoice\Renderer\Service\Pdf as PdfInvoiceRendererService;
use Orders\Controller\StoredFiltersController;
use CG\Order\Client\Service as OrderClientService;
use CG\Order\Service\Filter\StorageInterface as FilterStorageInterface;
use CG\Order\Client\Filter\Storage\Api as FilterStorage;
use Orders\Controller\BulkActionsController;
use Orders\Controller\CancelController;
use CG\Settings\Alias\Storage\Api as ShippingAliasStorage;
use CG\Order\Client\Tracking\Storage\Api as TrackingStorageApi;
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Account\Client\Storage\Api as AccountStorageApi;

return [
    'router' => [
        'routes' => [
            'Products' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/products',
                    'defaults' => [
                        'controller' => 'Products\Controller\Products',
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'sidebar' => 'products/products/sidebar'
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ajax' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/ajax',
                            'defaults' => [
                                'action' => 'jsonFilter',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filterId' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:filterId',
                                    'constraints' => [
                                        'filterId' => '.+'
                                    ],
                                    'defaults' => [
                                        'action' => 'jsonFilterId',
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'batch' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/batch',
                            'defaults' => array(
                                'controller' => BulkActionsController::class,
                                'action'     => 'batches',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'create' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                    'route'    => '/create',
                                    'defaults' => array(
                                        'action'     => 'batchOrderIds',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => [
                                    'filterId' => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => [
                                            'route' => '/:filterId',
                                            'constraints' => [
                                                'filterId' => '.+'
                                            ],
                                            'defaults' => [
                                                'action' => 'batchFilterId',
                                            ]
                                        ],
                                    ],
                                ],
                            ),
                            'unset' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/unset',
                                    'defaults' => [
                                        'action' => 'unBatchOrderIds'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'filterId' => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => [
                                            'route' => '/:filterId',
                                            'constraints' => [
                                                'filterId' => '.+'
                                            ],
                                            'defaults' => [
                                                'action' => 'unBatchFilterId',
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        )
                    ],
                    'order' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'priority' => -100,
                        'options' => [
                            'route' => '/:order',
                            'constraints' => [
                                'order' => '[0-9]*\-[a-zA-Z0-9_-]*'
                            ],
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
                            'tracking' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/tracking',
                                    'defaults' => [
                                        'controller' => Controller\TrackingController::class,
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'set' => [
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
                            ]
                        ]
                    ],
                    'dispatch' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/dispatch',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'dispatchOrderIds',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filterId' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:filterId',
                                    'constraints' => [
                                        'filterId' => '.+'
                                    ],
                                    'defaults' => [
                                        'action' => 'dispatchFilterId',
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'cancel' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/cancel',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'cancelOrderIds'
                            ]
                        ]
                    ],
                    'tag' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/tag',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                            ]
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'action' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:tagAction',
                                    'constraints' => [
                                        'tagAction' => 'append|remove'
                                    ],
                                    'defaults' => [
                                        'action' => 'tagOrderIds',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'filterId' => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => [
                                            'route' => '/:filterId',
                                            'constraints' => [
                                                'filterId' => '.+'
                                            ],
                                            'defaults' => [
                                                'action' => 'tagFilterId',
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ]
                    ],
                    'archive' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/archive',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'archiveOrderIds',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filterId' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:filterId',
                                    'constraints' => [
                                        'filterId' => '.+'
                                    ],
                                    'defaults' => [
                                        'action' => 'archiveFilterId',
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'invoice' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/invoice',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'invoiceOrderIds'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filterId' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:filterId',
                                    'constraints' => [
                                        'filterId' => '.+'
                                    ],
                                    'defaults' => [
                                        'action' => 'invoiceFilterId',
                                    ]
                                ],
                            ],
                            'invoice_demo' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/preview',
                                    'defaults' => [
                                        'action' => 'previewInvoice'
                                    ]
                                ],
                            ],
                            'invoice_check' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/check',
                                    'defaults' => [
                                        'action' => 'checkInvoicePrintingAllowed'
                                    ]
                                ],
                            ],
                        ]
                    ],
                    StoredFiltersController::ROUTE_SAVE => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/filter/save',
                            'defaults' => [
                                'controller' => StoredFiltersController::class,
                                'action' => 'saveFilter'
                            ]
                        ]
                    ],
                    StoredFiltersController::ROUTE_REMOVE => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/filter/remove',
                            'defaults' => [
                                'controller' => StoredFiltersController::class,
                                'action' => 'removeFilter'
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
            'Orders\Controller\Batch' => function($controllerManager) {
                    return $controllerManager->getServiceLocator()->get(Controller\BatchController::class);
            },
            'Orders\Controller\Address' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\AddressController::class);
            },
            'Orders\Controller\Preference' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\PreferenceController::class);
            },
            'Orders\Controller\Tag' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\TagController::class);
            },
            'Orders\Controller\Invoice' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\InvoiceController::class);
            },
        ],
        'invokables' => [],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
            PROJECT_ROOT . '/public' . Module::PUBLIC_FOLDER . 'template',
        ],
        'strategies' => [
            'ViewJsonStrategy',
            'CG_Mustache\View\Strategy'
        ],
    ],
    'di' => [
        'definition' => [
            'class' => [
                TableService::class => [
                    'methods' => [
                        'addOrderTableModifier' => [
                            'orderTableModifier' => [
                                'type' => TableService\OrdersTableModifierInterface::class,
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'instance' => [
            'aliases' => [
                'OrderRpcClient' => JsonRpcClient::class,
            ],
            'preferences' => [
                InvoiceRendererService::class => PdfInvoiceRendererService::class,
                FilterStorageInterface::class => FilterStorage::class,
            ],             
        ],
    ],
    'navigation' => array(
        'application-navigation' => array(
            'orders' => array(
                'label'  => 'Orders',
                'route'  => 'Orders',
                'sprite' => 'sprite-orders-18-white',
                'order'  => 10
            )
        )
    ),
];
