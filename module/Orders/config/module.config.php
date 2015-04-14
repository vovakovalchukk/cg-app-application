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
use CG\Product\Client\Service as ProductService;
use CG\Product\Storage\Api as ProductApiStorage;
use CG\Stock\Service as StockService;
use CG\Stock\Storage\Api as StockApiStorage;
use CG\Stock\Location\Service as LocationService;
use CG\Stock\Location\Storage\Api as LocationApiStorage;
use CG\Listing\Service as ListingService;
use CG\Image\Service as ImageService;
use CG\Listing\Storage\Api as ListingApiStorage;
use CG\Image\Storage\Api as ImageApiStorage;
use CG\Settings\PickList\Service as PickListSettingsService;
use CG\Settings\PickList\Storage\Api as PickListSettingsApiStorage;
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
use Orders\Controller\StoredBatchesController;
use CG\Settings\Alias\Storage\Api as ShippingAliasStorage;
use CG\Order\Client\Tracking\Storage\Api as TrackingStorageApi;
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Account\Client\Storage\Api as AccountStorageApi;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitApiStorage;
use Orders\Order\Invoice\ProgressStorage as OrderInvoiceProgressStorage;
use Orders\Order\PickList\ProgressStorage as OrderPickListProgressStorage;

return [
    'router' => [
        'routes' => [
            'Orders' => [
                'type' => 'literal',
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
                    'preference' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/preference',
                            'defaults' => [
                                'controller' => 'Orders\Controller\Preference',
                                'action' => 'save',
                            ]
                        ]
                    ],
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
                                        'filterId' => '[^/]+'
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
                            'invoice_progress' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/progress',
                                    'defaults' => [
                                        'action' => 'checkInvoiceGenerationProgress'
                                    ]
                                ],
                            ],
                            'invoice_bysku' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/bySku',
                                    'defaults' => [
                                        'action' => 'invoiceOrderIdsBySku'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'invoice_filter_bysku' => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => [
                                            'route' => '/:filterId',
                                            'constraints' => [
                                                'filterId' => '[^/]+'
                                            ],
                                            'defaults' => [
                                                'action' => 'invoiceFilterIdBySku'
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ],
                    'pick_list' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/picklist',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'pickListOrderIds'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filterId' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:filterId',
                                    'constraints' => [
                                        'filterId' => '[^/]+'
                                    ],
                                    'defaults' => [
                                        'action' => 'pickListFilterId',
                                    ]
                                ]
                            ],
                            'pick_list_check' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/check',
                                    'defaults' => [
                                        'action' => 'checkPickListPrintingAllowed'
                                    ]
                                ]
                            ],
                            'pick_list_progress' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/progress',
                                    'defaults' => [
                                        'action' => 'checkPickListGenerationProgress'
                                    ]
                                ]
                            ]
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
                    StoredBatchesController::ROUTE_REMOVE => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => 'batch/remove',
                            'defaults' => [
                                'controller' => StoredBatchesController::class,
                                'action' => 'removeBatch'
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
                'MustacheFormatters' => ViewModel::class,
                'MustacheTags' => ViewModel::class,
                'OrdersTable' => DataTable::class,
                'OrdersTableSettings' => DataTable\Settings::class,
                'OrdersTableInfiniteScroll' => DataTable\InfiniteScroll::class,
                'OrdersCheckboxColumnView' => ViewModel::class,
                'OrdersCheckboxColumn' => DataTable\Column::class,
                'OrdersCheckboxCheckAll' => DataTable\CheckAll::class,
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
                'OrdersFulfilmentChannelColumnView' => ViewModel::class,
                'OrdersFulfilmentChannelColumn' => DataTable\Column::class,
                'OrdersOptionsColumnView' => ViewModel::class,
                'OrdersOptionsColumn' => DataTable\Column::class,
                'OrderRpcClient' => JsonRpcClient::class,
            ],
            'preferences' => [
                InvoiceRendererService::class => PdfInvoiceRendererService::class,
                FilterStorageInterface::class => FilterStorage::class,
            ],
            TableService::class => [
                'parameters' => [
                    'ordersTable' => 'OrdersTable',
                ],
                'injections' => [
                    'addOrderTableModifier' => [
                        ['orderTableModifier' => TableService\OrdersTableMustacheFormatters::class],
                        ['orderTableModifier' => TableService\OrdersTableTagColumns::class],
                    ],
                ],
            ],
            TableService\OrdersTableMustacheFormatters::class => [
                'parameters' => [
                    'javascript' => 'MustacheFormatters'
                ],
            ],
            'MustacheFormatters' => [
                'parameters' => [
                    'template' => 'orders/orders/table/javascript/mustache-formatters.js',
                ],
            ],
            TableService\OrdersTableTagColumns::class => [
                'parameters' => [
                    'javascript' => 'MustacheTags'
                ],
            ],
            'MustacheTags' => [
                'parameters' => [
                    'template' => 'orders/orders/table/javascript/mustache-tags.js',
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
                    'addColumn' => [
                        ['column' => 'OrdersCheckboxColumn'],
                        ['column' => 'OrdersChannelColumn'],
                        ['column' => 'OrdersAccountColumn'],
                        ['column' => 'OrdersDateColumn'],
                        ['column' => 'OrdersIdColumn'],
                        ['column' => 'OrdersTotalColumn'],
                        ['column' => 'OrdersBuyerColumn'],
                        ['column' => 'OrdersStatusColumn'],
                        ['column' => 'OrdersBatchColumn'],
                        ['column' => 'OrdersMessagesColumn'],
                        ['column' => 'OrdersShippingColumn'],
                        ['column' => 'OrdersTagColumn'],
                        ['column' => 'OrdersFulfilmentChannelColumn'],
                        ['column' => 'OrdersOptionsColumn'],
                    ],
                    'setVariable' => [
                        ['name' => 'settings', 'value' => 'OrdersTableSettings']
                    ],
                ],
            ],
            'OrdersTableSettings' => [
                'parameters' => [
                    'infiniteScroll' => 'OrdersTableInfiniteScroll',
                    'language' => [
                        'sProcessing' => 'Loading More Orders',
                    ],
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
                    'sortable' => false,
                    'hideable' => false,
                    'checkAll' => 'OrdersCheckboxCheckAll'
                ],
            ],
            'OrdersCheckboxCheckAll' => [
                'parameters' => [
                    'checkboxes' => '.checkbox-id',
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
                    'class' => 'channel-col',
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
                    'class' => 'account-col',
                ],
            ],
            'OrdersDateColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Ordered'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersDateColumn' => [
                'parameters' => [
                    'column' => 'purchaseDate',
                    'viewModel' => 'OrdersDateColumnView',
                    'class' => 'orderdate-col',
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
                    'class' => 'orderid-col',
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
                    'class' => 'total-col',
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
                    'class' => 'buyer-col',
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
                    'class' => 'batch-col',
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
                    'class' => 'message-col',
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
                    'class' => 'shipping-col',
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
                    'viewModel' => 'OrdersTagColumnView',
                    'class' => 'tag-col',
                ]
            ],
            'OrdersFulfilmentChannelColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Fulfilment Channel'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersFulfilmentChannelColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'fulfilmentChannel',
                    'viewModel' => 'OrdersFulfilmentChannelColumnView',
                    'class' => 'order-fulfilment-col'
                ]
            ],
            'OrdersOptionsColumnView' => [
                'parameters' => [
                    'template' => 'table/column-picker.phtml',
                ],
            ],
            'OrdersOptionsColumn' => [
                'parameters' => [
                    'order' => 9999,
                    'viewModel' => 'OrdersOptionsColumnView',
                    'class' => 'options',
                    'defaultContent' => '',
                    'sortable' => false,
                    'hideable' => false
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
            ],
            OrderService::class => [
                'parameters' => [
                    'orderClient' => OrderClientService::class,
                    'orderRpcClient' => 'OrderRpcClient'
                ],
            ],
            'OrderRpcClient' => [
                'parameters' => [
                    'guzzle' => 'cg_app_rpc_guzzle'
                ]
            ],
            Page::class => [
                'parameters' => [
                    'height' => 0,
                    'width' => 0
                ],
            ],
            FilterStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ],
            ],
            ShippingAliasStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            TrackingService::class => [
                'parameters' => [
                    'repository' => TrackingStorageApi::class,
                    'accountStorage' => AccountStorageApi::class
                ]
            ],
            TrackingStorageApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            BulkActionsController::class => [
                'parameters' => [
                    'usageService' => 'order_count_usage_service'
                ]
            ],
            Controller\OrdersController::class => [
                'parameters' => [
                    'usageService' => 'order_count_usage_service'
                ]
            ],
            OrderInvoiceProgressStorage::class => [
                'parameters' => [
                    'predis' => 'unreliable_redis'
                ]
            ],
            OrderPickListProgressStorage::class => [
                'parameters' => [
                    'predis' => 'unreliable_redis'
                ]
            ],
            ProductApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ProductService::class => [
                'parameters' => [
                    'repository' => ProductApiStorage::class,
                    'stockStorage' => StockService::class,
                    'listingStorage' => ListingService::class,
                    'imageStorage' => ImageService::class
                ]
            ],
            StockService::class => [
                'parameter' => [
                    'repository' => StockApiStorage::class,
                    'locationStorage' => LocationService::class
                ]
            ],
            ListingService::class => [
                'parameter' => [
                    'repository' => ListingApiStorage::class
                ]
            ],
            ImageService::class => [
                'parameter' => [
                    'repository' => ImageApiStorage::class
                ]
            ],
            StockApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ListingApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ImageApiStorage::class => [
                'parameters' => [
                    'client' => 'image_guzzle'
                ]
            ],
            LocationService::class => [
                'parameter' => [
                    'repository' => LocationApiStorage::class
                ]
            ],
            LocationApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            StockService::class => [
                'parameter' => [
                    'repository' => StockApiStorage::class,
                    'locationStorage' => LocationService::class
                ]
            ],
            StockApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
            LocationService::class => [
                'parameter' => [
                    'repository' => LocationApiStorage::class
                ]
            ],
            LocationApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
            PickListSettingsApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            PickListSettingsService::class => [
                'parameters' => [
                    'repository' => PickListSettingsApiStorage::class
                ]
            ],
            OrganisationUnitApiStorage::class => [
                'parameters' => [
                    'client' => 'directory_guzzle'
                ]
            ],
            OrganisationUnitService::class => [
                'parameters' => [
                    'repository' => OrganisationUnitApiStorage::class
                ]
            ]
        ],
    ],
    'navigation' => array(
        'application-navigation' => array(
            'orders' => array(
                'label'  => 'Orders',
                'sprite' => 'sprite-orders-18-white',
                'order'  => 5,
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . '/orders'
            )
        )
    ),
];
