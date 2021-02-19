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
use CG\Product\StorageInterface as ProductStorage;
use CG\Stock\Service as StockService;
use CG\Stock\Storage\Api as StockApiStorage;
use CG\Stock\StorageInterface as StockStorage;
use CG\Stock\Location\Service as LocationService;
use CG\Stock\Location\Storage\Api as LocationApiStorage;
use CG\Listing\Service as ListingService;
use CG\Image\Service as ImageService;
use CG\Listing\Storage\Api as ListingApiStorage;
use CG\Listing\StorageInterface as ListingStorage;
use CG\Image\Storage\Api as ImageApiStorage;
use CG\Settings\PickList\Service as PickListSettingsService;
use CG\Settings\PickList\Storage\Api as PickListSettingsApiStorage;
use Zend\View\Model\ViewModel;
use Orders\Order\Service as OrderService;
use CG\Http\Rpc\Json\Client as JsonRpcClient;
use CG\Order\Client\Invoice\Renderer\ServiceInterface as InvoiceRendererService;
use CG\Order\Client\Invoice\Renderer\Service\Pdf as PdfInvoiceRendererService;
use Orders\Controller\StoredFiltersController;
use CG\Order\Client\Service as OrderClientService;
use CG\Order\Service\Filter\StorageInterface as FilterStorageInterface;
use CG\Order\Client\Filter\Storage\Api as FilterStorage;
use Orders\Controller\BulkActionsController;
use Orders\Controller\PartialRefundController;
use Orders\Controller\StoredBatchesController;
use Orders\Controller\BarcodeController;
use CG\Settings\Alias\Storage\Api as ShippingAliasStorage;
use CG\Order\Client\Tracking\Storage\Api as TrackingStorageApi;
use CG\Order\Shared\Tracking\StorageInterface as TrackingStorage;
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Account\Client\Storage\Api as AccountStorageApi;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitApiStorage;
use Orders\Order\Invoice\ProgressStorage as OrderInvoiceProgressStorage;
use Orders\Order\PickList\ProgressStorage as OrderPickListProgressStorage;
use Orders\Controller\OrderDetailsController;
use Zend\Mvc\Router\Http\Segment;

// Manual Orders
use Orders\Controller\ManualOrderController;

// Courier
use Orders\Controller\CourierController;
use Orders\Controller\CourierJsonController;
use Orders\Courier\Service as CourierService;
use Settings\Factory\SidebarNavFactory;

//linked products
use CG\Product\LinkLeaf\StorageInterface as ProductLinkLeafStorageInterface;
use CG\Product\LinkLeaf\Storage\Api as ProductLinkLeafApiStorage;

use CG\Locale\EUVATCodeChecker;

return [
    'service_manager' => [
        'factories' => [
            'courier-specifics-navigation'  => SidebarNavFactory::class,
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/orders/',
        ],
    ],
    'navigation' => [
        'courier-specifics-navigation' => [
            'couriers' => [
                'label' => 'Courier Labels',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    // Leave this here. Entries will be added dynamically.
                ]
            ]
        ]
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
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
                    'orderCounts' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/orderCounts',
                            'defaults' => [
                                'action' => 'orderCountsAjax',
                            ]
                        ],
                        'may_terminate' => true,
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
                    'update-column-order' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/update-column-order',
                            'defaults' => [
                                'action' => 'updateColumnOrder'
                            ]
                        ],
                        'may_terminate' => true
                    ],
                    'pdf-export' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/pdf-export',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'pdfExport'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'pdf-export-check' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/check',
                                    'defaults' => [
                                        'action' => 'checkPdfExportAllowed'
                                    ]
                                ],
                            ],
                            'pdf-export-progress' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/progress',
                                    'defaults' => [
                                        'action' => 'checkPdfExportGenerationProgress'
                                    ]
                                ],
                            ],
                        ]
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
                            'checkAssociation' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/checkAssociation',
                                    'defaults' => [
                                        'action' => 'areOrdersAssociatedWithAnyBatch'
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        )
                    ],
                    'barcode' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/barcode',
                            'defaults' => [
                                'controller' => 'Orders\Controller\Barcode',
                                'action' => 'submit',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'getDeferredColumnData' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/getDeferredColumnData',
                            'defaults' => [
                                'controller' => 'Orders\Controller\Orders',
                                'action' => 'getDeferredColumnData',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'order' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'priority' => -100,
                        'options' => [
                            'route' => '/:order',
                            'constraints' => [
                                'order' => '[0-9]*\-[a-zA-Z0-9_\- ]*'
                            ],
                            'defaults' => [
                                'controller' => OrderDetailsController::class,
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
                            'recipientVatNumber' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/recipientVatNumber',
                                    'defaults' => [
                                        'controller' => Controller\OrdersController::class,
                                        'action' => 'setRecipientVatNumber'
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
                                    'validate' => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/validate',
                                            'defaults' => [
                                                'action' => 'validate'
                                            ],
                                        ],
                                        'may_terminate' => true
                                    ],
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
                    'new' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/new',
                            'defaults' => [
                                'controller' => ManualOrderController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'create' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/create',
                                    'defaults' => [
                                        'action' => 'create',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            'duplicateExistingOrder' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/:order',
                                    'defaults' => [
                                        'controller' => ManualOrderController::class,
                                        'action' => 'duplicateExistingOrder',
                                    ],
                                    'constraints' => [
                                        'order' => '[0-9]*\-[a-zA-Z0-9_\- ]*'
                                    ],
                                ],
                            ],
                        ]
                    ],
                    'pay' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/pay',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'payForOrder'
                            ]
                        ]
                    ],
                    'unlink' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/unlink',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'unlinkOrder'
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
                    'partialRefund' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/partialRefund',
                            'defaults' => [
                                'controller' => PartialRefundController::class,
                                'action' => 'partialRefund'
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
                            'unarchive' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/unarchive',
                                    'defaults' => [
                                        'action' => 'unarchiveOrderIds',
                                    ],
                                ],
                            ],
                            'filterId' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'priority' => -100,
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
                            'invoice_bytitle' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/byTitle',
                                    'defaults' => [
                                        'action' => 'invoiceOrderIdsByTitle'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'invoice_filter_bytitle' => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => [
                                            'route' => '/:filterId',
                                            'constraints' => [
                                                'filterId' => '[^/]+'
                                            ],
                                            'defaults' => [
                                                'action' => 'invoiceFilterIdByTitle'
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                            'invoice_email' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/email',
                                    'defaults' => [
                                        'action' => 'emailInvoice'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'invoice_filter_email' => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => [
                                            'route' => '/:filterId',
                                            'constraints' => [
                                                'filterId' => '[^/]+'
                                            ],
                                            'defaults' => [
                                                'action' => 'emailInvoiceFilter'
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
                    'to_csv' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/toCsv',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'toCsvOrderIds'
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
                                        'action' => 'toCsvFilterId',
                                    ]
                                ]
                            ],
                            'to_csv_check' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/check',
                                    'defaults' => [
                                        'action' => 'checkCsvGenerationAllowed'
                                    ]
                                ]
                            ],
                            'to_csv_progress' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/progress',
                                    'defaults' => [
                                        'action' => 'checkCsvGenerationProgress'
                                    ]
                                ]
                            ],
                            'to_csv_order_data' => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/orderData',
                                    'defaults' => [
                                        'action' => 'toCsvOrderDataOnlyOrderIds'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'to_csv_filter_id' => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => [
                                            'route' => '/:filterId',
                                            'constraints' => [
                                                'filterId' => '[^/]+'
                                            ],
                                            'defaults' => [
                                                'action' => 'toCsvOrderDataOnlyFilterId'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    CourierController::ROUTE => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => CourierController::ROUTE_URI,
                            'defaults' => [
                                'controller' => CourierController::class,
                                'action' => 'index',
                                'breadcrumbs' => false,
                                'sidebar' => false,
                                'subHeader' => false,
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            CourierJsonController::ROUTE_SERVICES => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/services',
                                    'defaults' => [
                                        'controller' => CourierJsonController::class,
                                        'action' => 'servicesOptions',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            CourierJsonController::ROUTE_SERVICES_FOR_ORDERS => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/servicesForOrders',
                                    'defaults' => [
                                        'controller' => CourierJsonController::class,
                                        'action' => 'servicesOptionsForOrders',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            CourierJsonController::ROUTE_CHECK_SERVICES_FOR_ORDERS => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/checkServicesForOrders',
                                    'defaults' => [
                                        'controller' => CourierJsonController::class,
                                        'action' => 'checkServicesOptionsForOrders',
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            CourierController::ROUTE_REVIEW => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => CourierController::ROUTE_REVIEW_URI,
                                    'defaults' => [
                                        'action' => 'review',
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    CourierJsonController::ROUTE_REVIEW_LIST => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierJsonController::ROUTE_REVIEW_LIST_URI,
                                            'defaults' => [
                                                'controller' => CourierJsonController::class,
                                                'action' => 'reviewList',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ]
                                ]
                            ],
                            CourierController::ROUTE_SPECIFICS => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => CourierController::ROUTE_SPECIFICS_URI,
                                    'defaults' => [
                                        'action' => 'specifics',
                                        'breadcrumbs' => false,
                                        'sidebar' => 'orders/courier/specifics/sidebar',
                                        'subHeader' => false,
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    CourierJsonController::ROUTE_SPECIFICS_LIST => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierJsonController::ROUTE_SPECIFICS_LIST_URI,
                                            'defaults' => [
                                                'controller' => CourierJsonController::class,
                                                'action' => 'specificsList',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierJsonController::ROUTE_SPECIFICS_OPTIONS => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/options',
                                            'defaults' => [
                                                'controller' => CourierJsonController::class,
                                                'action' => 'options',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierJsonController::ROUTE_SPECIFICS_OPTION_DATA => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/optionData',
                                            'defaults' => [
                                                'controller' => CourierJsonController::class,
                                                'action' => 'optionData',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierJsonController::ROUTE_SPECIFICS_FETCH_SHIPPING_LEDGER_BALANCE => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/fetchShippingLedgerBalance',
                                            'defaults' => [
                                                'controller' => CourierJsonController::class,
                                                'action' => 'fetchShippingLedgerBalance',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                ]
                            ],
                            CourierController::ROUTE_LABEL => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => CourierController::ROUTE_LABEL_URI,
                                    'defaults' => [
                                        'controller' => CourierJsonController::class,
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    CourierJsonController::ROUTE_LABEL_CREATE => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierJsonController::ROUTE_LABEL_CREATE_URI,
                                            'defaults' => [
                                                'action' => 'createLabel',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierController::ROUTE_LABEL_EXPORT => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierController::ROUTE_LABEL_EXPORT_URI,
                                            'defaults' => [
                                                'controller' => CourierController::class,
                                                'action' => 'export',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierController::ROUTE_LABEL_PRINT => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierController::ROUTE_LABEL_PRINT_URI,
                                            'defaults' => [
                                                'controller' => CourierController::class,
                                                'action' => 'printLabel',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierJsonController::ROUTE_LABEL_CANCEL => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierJsonController::ROUTE_LABEL_CANCEL_URI,
                                            'defaults' => [
                                                'action' => 'cancel',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierJsonController::ROUTE_LABEL_DISPATCH => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierJsonController::ROUTE_LABEL_DISPATCH_URI,
                                            'defaults' => [
                                                'action' => 'dispatch',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierJsonController::ROUTE_LABEL_READY_CHECK => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierJsonController::ROUTE_LABEL_READY_CHECK_URI,
                                            'defaults' => [
                                                'action' => 'readyCheck',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierJsonController::ROUTE_LABEL_FETCH_RATES => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/fetchRates',
                                            'defaults' => [
                                                'action' => 'fetchRates',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                ]
                            ],
                            CourierJsonController::ROUTE_MANIFEST => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => CourierJsonController::ROUTE_MANIFEST_URI,
                                    'defaults' => [
                                        'controller' => CourierJsonController::class,
                                        'action' => 'createManifest',
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    CourierJsonController::ROUTE_MANIFEST_ACCOUNTS => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierJsonController::ROUTE_MANIFEST_ACCOUNTS_URI,
                                            'defaults' => [
                                                'controller' => CourierJsonController::class,
                                                'action' => 'manifestAccounts',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierJsonController::ROUTE_MANIFEST_DETAILS => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierJsonController::ROUTE_MANIFEST_DETAILS_URI,
                                            'defaults' => [
                                                'controller' => CourierJsonController::class,
                                                'action' => 'manifestDetails',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierJsonController::ROUTE_MANIFEST_HISTORIC => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => CourierJsonController::ROUTE_MANIFEST_HISTORIC_URI,
                                            'defaults' => [
                                                'controller' => CourierJsonController::class,
                                                'action' => 'historicManifests',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    CourierController::ROUTE_MANIFEST_PRINT => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'priority' => -100,
                                        'options' => [
                                            'route' => CourierController::ROUTE_MANIFEST_PRINT_URI,
                                            'constraints' => [
                                                'manifestId' => '.+'
                                            ],
                                            'defaults' => [
                                                'controller' => CourierController::class,
                                                'action' => 'printManifest',
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                ]
                            ],
                        ]
                    ],
                    'bulkActionFilter' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/bulkActionFilter',
                            'defaults' => [
                                'controller' => BulkActionsController::class,
                                'action' => 'saveFilter',
                            ]
                        ],
                        'may_terminate' => true,
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
                    ],

                    Controller\OrdersController::ROUTE_IMAGES => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/images',
                            'defaults' => [
                                'controller' => Controller\OrdersController::class,
                                'action' => 'imagesForOrders'
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
            'Orders\Controller\Barcode' => function($controllerManager) {
                return $controllerManager->getServiceLocator()->get(Controller\BarcodeController::class);
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
                'UpdateColumnOrder' => ViewModel::class,
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
                'OrdersGiftColumnView' => ViewModel::class,
                'OrdersGiftColumn' => DataTable\Column::class,
                'OrdersShippingColumnView' => ViewModel::class,
                'OrdersShippingColumn' => DataTable\Column::class,
                'OrdersTagColumnView' => ViewModel::class,
                'OrdersTagColumn' => DataTable\Column::class,
                'OrdersFulfilmentChannelColumnView' => ViewModel::class,
                'OrdersFulfilmentChannelColumn' => DataTable\Column::class,
                'OrdersInvoiceNumberColumnView' => ViewModel::class,
                'OrdersInvoiceNumberColumn' => DataTable\Column::class,
                'OrdersAlertColumnView' => ViewModel::class,
                'OrdersAlertColumn' => DataTable\Column::class,
                'OrdersOptionsColumnView' => ViewModel::class,
                'OrdersOptionsColumn' => DataTable\Column::class,
                'OrdersShippingPriceColumnView' => ViewModel::class,
                'OrdersShippingPriceColumn' => DataTable\Column::class,
                'OrdersTotalDiscountColumnView' => ViewModel::class,
                'OrdersTotalDiscountColumn' => DataTable\Column::class,
                'OrdersPaymentDateColumnView' => ViewModel::class,
                'OrdersPaymentDateColumn' => DataTable\Column::class,
                'OrdersPrintedDateColumnView' => ViewModel::class,
                'OrdersPrintedDateColumn' => DataTable\Column::class,
                'OrdersDispatchedDateColumnView' => ViewModel::class,
                'OrdersDispatchedDateColumn' => DataTable\Column::class,
                'OrdersLabelCreatedDateColumnView' => ViewModel::class,
                'OrdersLabelCreatedDateColumn' => DataTable\Column::class,
                'OrdersInvoiceEmailedDateColumnView' => ViewModel::class,
                'OrdersInvoiceEmailedDateColumn' => DataTable\Column::class,
                'OrdersPaymentMethodColumnView' => ViewModel::class,
                'OrdersPaymentMethodColumn' => DataTable\Column::class,
                'OrdersPaymentReferenceColumnView' => ViewModel::class,
                'OrdersPaymentReferenceColumn' => DataTable\Column::class,
                'OrdersTrackingInfoColumnView' => ViewModel::class,
                'OrdersTrackingInfoColumn' => DataTable\Column::class,
                'OrdersPostcodeColumnView' => ViewModel::class,
                'OrdersPostcodeColumn' => DataTable\Column::class,
                'OrdersProductImageColumnView' => ViewModel::class,
                'OrdersProductImageColumn' => DataTable\Column::class,
                'OrdersCustomisationColumnView' => ViewModel::class,
                'OrdersCustomisationColumn' => DataTable\Column::class,

                'OrderRpcClient' => JsonRpcClient::class,

                'CourierReviewTable' => DataTable::class,
                'CourierReviewTableSettings' => DataTable\Settings::class,
                'CourierReviewBuyerOrderColumnView' => ViewModel::class,
                'CourierReviewBuyerOrderColumn' => DataTable\Column::class,
                'CourierReviewShippingMethodColumnView' => ViewModel::class,
                'CourierReviewShippingMethodColumn' => DataTable\Column::class,
                'CourierReviewCourierColumnView' => ViewModel::class,
                'CourierReviewCourierColumn' => DataTable\Column::class,
                'CourierReviewServiceColumnView' => ViewModel::class,
                'CourierReviewServiceColumn' => DataTable\Column::class,
                'CourierReviewItemImageColumnView' => ViewModel::class,
                'CourierReviewItemImageColumn' => DataTable\Column::class,
                'CourierReviewItemColumnView' => ViewModel::class,
                'CourierReviewItemColumn' => DataTable\Column::class,

                'CourierSpecificsTable' => DataTable::class,
                'CourierSpecificsTableSettings' => DataTable\Settings::class,
                'CourierSpecificsBuyerOrderColumnView' => ViewModel::class,
                'CourierSpecificsBuyerOrderColumn' => DataTable\Column::class,
                'CourierSpecificsShippingMethodColumnView' => ViewModel::class,
                'CourierSpecificsShippingMethodColumn' => DataTable\Column::class,
                'CourierSpecificsServiceColumnView' => ViewModel::class,
                'CourierSpecificsServiceColumn' => DataTable\Column::class,
                'CourierSpecificsParcelsColumnView' => ViewModel::class,
                'CourierSpecificsParcelsColumn' => DataTable\Column::class,
                'CourierSpecificsItemImageColumnView' => ViewModel::class,
                'CourierSpecificsItemImageColumn' => DataTable\Column::class,
                'CourierSpecificsItemColumnView' => ViewModel::class,
                'CourierSpecificsItemColumn' => DataTable\Column::class,
                'CourierSpecificsActionsColumnView' => ViewModel::class,
                'CourierSpecificsActionsColumn' => DataTable\Column::class,
                // Optional columns, added to table dynamically as required
                'CourierSpecificsCollectionDateColumnView' => ViewModel::class,
                'CourierSpecificsCollectionDateColumn' => DataTable\Column::class,
                'CourierSpecificsCollectionTimeColumnView' => ViewModel::class,
                'CourierSpecificsCollectionTimeColumn' => DataTable\Column::class,
                'CourierSpecificsWeightColumnView' => ViewModel::class,
                'CourierSpecificsWeightColumn' => DataTable\Column::class,
                'CourierSpecificsHeightColumnView' => ViewModel::class,
                'CourierSpecificsHeightColumn' => DataTable\Column::class,
                'CourierSpecificsWidthColumnView' => ViewModel::class,
                'CourierSpecificsWidthColumn' => DataTable\Column::class,
                'CourierSpecificsLengthColumnView' => ViewModel::class,
                'CourierSpecificsLengthColumn' => DataTable\Column::class,
                'CourierSpecificsInsuranceColumnView' => ViewModel::class,
                'CourierSpecificsInsuranceColumn' => DataTable\Column::class,
                'CourierSpecificsInsuranceMonetaryColumnView' => ViewModel::class,
                'CourierSpecificsInsuranceMonetaryColumn' => DataTable\Column::class,
                'CourierSpecificsInsuranceOptionsColumnView' => ViewModel::class,
                'CourierSpecificsInsuranceOptionsColumn' => DataTable\Column::class,
                'CourierSpecificsSignatureColumnView' => ViewModel::class,
                'CourierSpecificsSignatureColumn' => DataTable\Column::class,
                'CourierSpecificsDeliveryInstructionsColumnView' => ViewModel::class,
                'CourierSpecificsDeliveryInstructionsColumn' => DataTable\Column::class,
                'CourierSpecificsItemParcelAssignmentColumnView' => ViewModel::class,
                'CourierSpecificsItemParcelAssignmentColumn' => DataTable\Column::class,
                'CourierSpecificsPackageTypeColumnView' => ViewModel::class,
                'CourierSpecificsPackageTypeColumn' => DataTable\Column::class,
                'CourierSpecificsAddOnsColumnView' => ViewModel::class,
                'CourierSpecificsAddOnsColumn' => DataTable\Column::class,
                'CourierSpecificsDeliveryExperienceColumnView' => ViewModel::class,
                'CourierSpecificsDeliveryExperienceColumn' => DataTable\Column::class,
                'CourierSpecificsCourierPickupColumnView' => ViewModel::class,
                'CourierSpecificsCourierPickupColumn' => DataTable\Column::class,
                'CourierSpecificsSaturdayColumnView' => ViewModel::class,
                'CourierSpecificsSaturdayColumn' => DataTable\Column::class,
                'CourierSpecificsCostColumnView' => ViewModel::class,
                'CourierSpecificsCostColumn' => DataTable\Column::class,
                'CourierSpecificsHarmonisedSystemCodeColumnView' => ViewModel::class,
                'CourierSpecificsHarmonisedSystemCodeColumn' => DataTable\Column::class,
                'CourierSpecificsHarmonisedSystemCodeDescriptionColumnView' => ViewModel::class,
                'CourierSpecificsHarmonisedSystemCodeDescriptionColumn' => DataTable\Column::class,
                'CourierSpecificsCountryOfOriginColumnView' => ViewModel::class,
                'CourierSpecificsCountryOfOriginColumn' => DataTable\Column::class,
                'CourierSpecificsEoriNumberColumnView' => ViewModel::class,
                'CourierSpecificsEoriNumberColumn' => DataTable\Column::class,
                'CourierSpecificsTermsOfDeliveryColumnView' => ViewModel::class,
                'CourierSpecificsTermsOfDeliveryColumn' => DataTable\Column::class,
            ],
            'preferences' => [
                InvoiceRendererService::class => PdfInvoiceRendererService::class,
                FilterStorageInterface::class => FilterStorage::class,
                ProductStorage::class => ProductApiStorage::class,
                StockStorage::class => StockApiStorage::class,
                ListingStorage::class => ListingApiStorage::class,
                TrackingStorage::class => TrackingStorageApi::class,
                ProductLinkLeafStorageInterface::class => ProductLinkLeafApiStorage::class
            ],
            ProductLinkLeafApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            TableService::class => [
                'parameters' => [
                    'ordersTable' => 'OrdersTable',
                ],
                'injections' => [
                    'addOrderTableModifier' => [
                        ['orderTableModifier' => TableService\OrdersTableUpdateColumnOrder::class],
                        ['orderTableModifier' => TableService\OrdersTableMustacheFormatters::class],
                        ['orderTableModifier' => TableService\OrdersTableTagColumns::class],
                        ['orderTableModifier' => TableService\OrdersTableUserPreferences::class],
                    ],
                ],
            ],
            TableService\OrdersTableUpdateColumnOrder::class => [
                'parameters' => [
                    'javascript' => 'UpdateColumnOrder',
                ],
            ],
            'UpdateColumnOrder' => [
                'parameters' => [
                    'variables' => ['route' => 'Orders/update-column-order'],
                    'template' => 'orders/orders/table/javascript/update-column-order.js',
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
                        ['column' => 'OrdersProductImageColumn'],
                        ['column' => 'OrdersTotalColumn'],
                        ['column' => 'OrdersBuyerColumn'],
                        ['column' => 'OrdersStatusColumn'],
                        ['column' => 'OrdersBatchColumn'],
                        ['column' => 'OrdersMessagesColumn'],
                        ['column' => 'OrdersGiftColumn'],
                        ['column' => 'OrdersShippingColumn'],
                        ['column' => 'OrdersTagColumn'],
                        ['column' => 'OrdersFulfilmentChannelColumn'],
                        ['column' => 'OrdersInvoiceNumberColumn'],
                        ['column' => 'OrdersAlertColumn'],
                        ['column' => 'OrdersOptionsColumn'],
                        ['column' => 'OrdersShippingPriceColumn'],
                        ['column' => 'OrdersTotalDiscountColumn'],
                        ['column' => 'OrdersPaymentDateColumn'],
                        ['column' => 'OrdersPrintedDateColumn'],
                        ['column' => 'OrdersDispatchedDateColumn'],
                        ['column' => 'OrdersLabelCreatedDateColumn'],
                        ['column' => 'OrdersInvoiceEmailedDateColumn'],
                        ['column' => 'OrdersPaymentMethodColumn'],
                        ['column' => 'OrdersPaymentReferenceColumn'],
                        ['column' => 'OrdersTrackingInfoColumn'],
                        ['column' => 'OrdersPostcodeColumn'],
                        ['column' => 'OrdersCustomisationColumn'],
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
                    'sortable' => false,
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
                    'sortable' => false,
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
                    'sortable' => true,
                ],
            ],
            'OrdersIdColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Order ID /<br>Product Information'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersIdColumn' => [
                'parameters' => [
                    'column' => 'externalId',
                    'viewModel' => 'OrdersIdColumnView',
                    'class' => 'orderid-col',
                    'sortable' => false,
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
                    'sortable' => false,
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
                    'sortable' => false,
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
                    'sortable' => false,
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
                    'sortable' => false,
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
                    'sortable' => false,
                ],
            ],
            'OrdersGiftColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Gift'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersGiftColumn' => [
                'parameters' => [
                    'column' => 'giftMessage',
                    'viewModel' => 'OrdersGiftColumnView',
                    'class' => 'gift-col',
                    'sortable' => false,
                ],
            ],
            'OrdersShippingColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Shipping<br>Method'],
                    'template' => 'value.phtml',
                ],
            ],
            'OrdersShippingColumn' => [
                'parameters' => [
                    'column' => 'shippingMethod',
                    'viewModel' => 'OrdersShippingColumnView',
                    'class' => 'shipping-col',
                    'sortable' => false,
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
                    'sortable' => false,
                ]
            ],
            'OrdersFulfilmentChannelColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Fulfilment<br>Channel'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersFulfilmentChannelColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'fulfilmentChannel',
                    'viewModel' => 'OrdersFulfilmentChannelColumnView',
                    'class' => 'order-fulfilment-col',
                    'sortable' => false,
                ]
            ],
            'OrdersInvoiceNumberColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Inv'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersInvoiceNumberColumn' => [
                'parameters' => [
                    'visible' => true,
                    'column' => 'invoiceNumber',
                    'viewModel' => 'OrdersInvoiceNumberColumnView',
                    'class' => 'order-invoice-number-col',
                    'sortable' => false,
                ]
            ],
            'OrdersAlertColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Alerts'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersAlertColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'alerts',
                    'viewModel' => 'OrdersAlertColumnView',
                    'class' => 'order-alert-col',
                    'sortable' => false,
                ]
            ],
            'OrdersOptionsColumnView' => [
                'parameters' => [
                    'template' => 'table/column-picker.phtml',
                    'variables' => [
                        'persistUri' => '/orders/update-columns',
                    ]
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
            'OrdersShippingPriceColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Shipping<br>Price'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersShippingPriceColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'shippingPrice',
                    'viewModel' => 'OrdersShippingPriceColumnView',
                    'class' => 'order-shipping-price-col',
                    'sortable' => true,
                ]
            ],
            'OrdersTotalDiscountColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Total<br>Discount'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersTotalDiscountColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'totalDiscount',
                    'viewModel' => 'OrdersTotalDiscountColumnView',
                    'class' => 'order-total-discount-col',
                    'sortable' => true,
                ]
            ],
            'OrdersPaymentDateColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Payment<br>Date'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersPaymentDateColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'paymentDate',
                    'viewModel' => 'OrdersPaymentDateColumnView',
                    'class' => 'order-payment-date-col',
                    'sortable' => true,
                ]
            ],
            'OrdersPrintedDateColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Printed<br>Date'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersPrintedDateColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'printedDate',
                    'viewModel' => 'OrdersPrintedDateColumnView',
                    'class' => 'order-printed-date-col',
                    'sortable' => true,
                ]
            ],
            'OrdersDispatchedDateColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Dispatched<br>Date'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersDispatchedDateColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'dispatchDate',
                    'viewModel' => 'OrdersDispatchedDateColumnView',
                    'class' => 'order-dispatched-date-col',
                    'sortable' => true,
                ]
            ],
            'OrdersLabelCreatedDateColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Shipping Label<br>Created Date'],

                    'template' => 'value.phtml',
                ]
            ],
            'OrdersLabelCreatedDateColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'labelCreatedDate',
                    'viewModel' => 'OrdersLabelCreatedDateColumnView',
                    'class' => 'order-label-created-date-col',
                ]
            ],
            'OrdersInvoiceEmailedDateColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Invoice<br>Emailed'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersInvoiceEmailedDateColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'emailDate',
                    'viewModel' => 'OrdersInvoiceEmailedDateColumnView',
                    'class' => 'order-invoice-emailed-date-col',
                    'sortable' => true,
                ]
            ],
            'OrdersPaymentMethodColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Payment<br>Method'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersPaymentMethodColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'paymentMethod',
                    'viewModel' => 'OrdersPaymentMethodColumnView',
                    'class' => 'order-payment-method-col',
                    'sortable' => false,
                ]
            ],
            'OrdersPaymentReferenceColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Payment<br>Reference'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersPaymentReferenceColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'paymentReference',
                    'viewModel' => 'OrdersPaymentReferenceColumnView',
                    'class' => 'order-payment-reference-col',
                    'sortable' => false,
                ]
            ],
            'OrdersTrackingInfoColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Tracking<br>Info'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersTrackingInfoColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'trackingInfo',
                    'viewModel' => 'OrdersTrackingInfoColumnView',
                    'class' => 'order-tracking-info-col',
                    'sortable' => false,
                ]
            ],
            'OrdersPostcodeColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Postcode'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersPostcodeColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'shippingAddressPostcode',
                    'viewModel' => 'OrdersPostcodeColumnView',
                    'class' => 'order-postcode-col',
                    'sortable' => false,
                ]
            ],
            'OrdersProductImageColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Product<br>Image'],
                    // Note: this is NOT using the standard template but a bespoke one that loads up some JS
                    'template' => 'orders/orders/table/columns/productImage.phtml',
                ]
            ],
            'OrdersProductImageColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'image',
                    'viewModel' => 'OrdersProductImageColumnView',
                    'class' => 'order-product-image-col',
                    'sortable' => false,
                ]
            ],
            'OrdersCustomisationColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Customisation'],
                    'template' => 'value.phtml',
                ]
            ],
            'OrdersCustomisationColumn' => [
                'parameters' => [
                    'visible' => false,
                    'column' => 'customisation',
                    'viewModel' => 'OrdersCustomisationColumnView',
                    'class' => 'order-customisation-col',
                    'sortable' => false,
                ]
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
            ],
            BarcodeController::class => [
                'parameters' => [
                    'config' => 'app_config',
                ]
            ],

            CourierService::class => [
                'parameters' => [
                    'config' => 'app_config',
                ]
            ],
            CourierController::class => [
                'parameters' => [
                    'reviewTable' => 'CourierReviewTable',
                    'specificsTable' => 'CourierSpecificsTable',
                ]
            ],
            'CourierReviewTable' => [
                'parameters' => [
                    'variables' => [
                        'sortable' => 'false',
                        'id' => 'datatable',
                        'class' => 'fixed-header fixed-footer',
                        'width' => '100%'
                    ],
                ],
                'injections' => [
                    'addColumn' => [
                        ['column' => 'CourierReviewBuyerOrderColumn'],
                        ['column' => 'CourierReviewShippingMethodColumn'],
                        ['column' => 'CourierReviewCourierColumn'],
                        ['column' => 'CourierReviewServiceColumn'],
                        ['column' => 'CourierReviewItemImageColumn'],
                        ['column' => 'CourierReviewItemColumn'],
                    ],
                    'setVariable' => [
                        ['name' => 'settings', 'value' => 'CourierReviewTableSettings']
                    ],
                ],
            ],
            'CourierReviewTableSettings' => [
                'parameters' => [
                    'scrollHeightAuto' => false,
                    'footer' => false,
                    'pagination' => false,
                    'tableOptions' => 'rt<"table-footer" ilp>',
                    'language' => [
                      'sLengthMenu' => '<span class="show">Show</span> _MENU_'
                    ],
                ]
            ],
            'CourierReviewBuyerOrderColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Buyer /<br>Order ID'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierReviewBuyerOrderColumn' => [
                'parameters' => [
                    'column' => 'buyerOrder',
                    'viewModel' => 'CourierReviewBuyerOrderColumnView',
                    'class' => 'buyerOrder-col',
                    'sortable' => false,
                ],
            ],
            'CourierReviewShippingMethodColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Shipping <br>Method'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierReviewShippingMethodColumn' => [
                'parameters' => [
                    'column' => 'shippingMethod',
                    'viewModel' => 'CourierReviewShippingMethodColumnView',
                    'class' => 'shippingMethod-col',
                    'sortable' => false,
                ],
            ],
            'CourierReviewCourierColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Courier'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierReviewCourierColumn' => [
                'parameters' => [
                    'column' => 'courier',
                    'viewModel' => 'CourierReviewCourierColumnView',
                    'class' => 'courier-col',
                    'sortable' => false,
                ],
            ],
            'CourierReviewServiceColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Service'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierReviewServiceColumn' => [
                'parameters' => [
                    'column' => 'service',
                    'viewModel' => 'CourierReviewServiceColumnView',
                    'class' => 'service-col',
                    'sortable' => false,
                ],
            ],
            'CourierReviewItemImageColumnView' => [
                'parameters' => [
                    'variables' => ['value' => ''],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierReviewItemImageColumn' => [
                'parameters' => [
                    'column' => 'itemImage',
                    'viewModel' => 'CourierReviewItemImageColumnView',
                    'class' => 'itemImage-col',
                    'sortable' => false,
                ],
            ],
            'CourierReviewItemColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Item'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierReviewItemColumn' => [
                'parameters' => [
                    'column' => 'item',
                    'viewModel' => 'CourierReviewItemColumnView',
                    'class' => 'item-col',
                    'sortable' => false,
                ],
            ],

            'CourierSpecificsTable' => [
                'parameters' => [
                    'variables' => [
                        'sortable' => 'false',
                        'id' => 'datatable',
                        'class' => 'fixed-header fixed-footer',
                        'width' => '100%'
                    ],
                ],
                'injections' => [
                    'addColumn' => [
                        ['column' => 'CourierSpecificsBuyerOrderColumn'],
                        ['column' => 'CourierSpecificsShippingMethodColumn'],
                        ['column' => 'CourierSpecificsServiceColumn'],
                        ['column' => 'CourierSpecificsItemImageColumn'],
                        ['column' => 'CourierSpecificsItemColumn'],
                    ],
                    'setVariable' => [
                        ['name' => 'settings', 'value' => 'CourierSpecificsTableSettings']
                    ],
                ],
            ],
            'CourierSpecificsTableSettings' => [
                'parameters' => [
                    'scrollHeightAuto' => false,
                    'footer' => false,
                    'pagination' => false,
                    'tableOptions' => 'rt<"table-footer" ilp>',
                    'language' => [
                      'sLengthMenu' => '<span class="show">Show</span> _MENU_'
                    ],
                ]
            ],
            'CourierSpecificsBuyerOrderColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Buyer /<br />Order ID'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsBuyerOrderColumn' => [
                'parameters' => [
                    'column' => 'buyerOrder',
                    'viewModel' => 'CourierSpecificsBuyerOrderColumnView',
                    'class' => 'buyerOrder-col',
                    'sortable' => false,
                    'order' => 10,
                ],
            ],
            'CourierSpecificsShippingMethodColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Shipping<br/>Method'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsShippingMethodColumn' => [
                'parameters' => [
                    'column' => 'shippingMethod',
                    'viewModel' => 'CourierSpecificsShippingMethodColumnView',
                    'class' => 'shippingMethod-col',
                    'sortable' => false,
                    'order' => 20,
                ],
            ],
            'CourierSpecificsServiceColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Service'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsServiceColumn' => [
                'parameters' => [
                    'column' => 'service',
                    'viewModel' => 'CourierSpecificsServiceColumnView',
                    'class' => 'service-col',
                    'sortable' => false,
                    'order' => 30,
                ],
            ],
            'CourierSpecificsParcelsColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'No.<br/>Parcels'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsParcelsColumn' => [
                'parameters' => [
                    'column' => 'parcels',
                    'viewModel' => 'CourierSpecificsParcelsColumnView',
                    'class' => 'parcels-col',
                    'sortable' => false,
                    'order' => 40,
                    'width' => '100px',
                ],
            ],
            'CourierSpecificsCollectionDateColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Collection'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsCollectionDateColumn' => [
                'parameters' => [
                    'column' => 'collectionDate',
                    'viewModel' => 'CourierSpecificsCollectionDateColumnView',
                    'class' => 'collectionDate-col',
                    'sortable' => false,
                    'order' => 50,
                ],
            ],
            'CourierSpecificsCollectionTimeColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Time'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsCollectionTimeColumn' => [
                'parameters' => [
                    'column' => 'collectionTime',
                    'viewModel' => 'CourierSpecificsCollectionTimeColumnView',
                    'class' => 'collectionTime-col',
                    'sortable' => false,
                    'order' => 51,
                ],
            ],
            'CourierSpecificsItemImageColumnView' => [
                'parameters' => [
                    'variables' => ['value' => ''],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsItemImageColumn' => [
                'parameters' => [
                    'column' => 'itemImage',
                    'viewModel' => 'CourierSpecificsItemImageColumnView',
                    'class' => 'itemImage-col',
                    'sortable' => false,
                    'order' => 60,
                ],
            ],
            'CourierSpecificsItemColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Item'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsItemColumn' => [
                'parameters' => [
                    'column' => 'item',
                    'viewModel' => 'CourierSpecificsItemColumnView',
                    'class' => 'item-col',
                    'sortable' => false,
                    'order' => 70,
                ],
            ],
            'CourierSpecificsActionsColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Actions'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsActionsColumn' => [
                'parameters' => [
                    'column' => 'actions',
                    'viewModel' => 'CourierSpecificsActionsColumnView',
                    'class' => 'actions-col',
                    'sortable' => false,
                    'order' => 999,
                ],
            ],
            // Optional columns, will be added to table dynamically as required
            'CourierSpecificsWeightColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Weight<br />({unit})'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsWeightColumn' => [
                'parameters' => [
                    'column' => 'weight',
                    'viewModel' => 'CourierSpecificsWeightColumnView',
                    'class' => 'weight-col',
                    'sortable' => false,
                    'order' => 80,
                ],
            ],
            'CourierSpecificsHeightColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Height<br />({unit})'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsHeightColumn' => [
                'parameters' => [
                    'column' => 'height',
                    'viewModel' => 'CourierSpecificsHeightColumnView',
                    'class' => 'height-col',
                    'sortable' => false,
                    'order' => 90,
                ],
            ],
            'CourierSpecificsWidthColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Width<br />({unit})'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsWidthColumn' => [
                'parameters' => [
                    'column' => 'width',
                    'viewModel' => 'CourierSpecificsWidthColumnView',
                    'class' => 'width-col',
                    'sortable' => false,
                    'order' => 100,
                ],
            ],
            'CourierSpecificsLengthColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Length<br />({unit})'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsLengthColumn' => [
                'parameters' => [
                    'column' => 'length',
                    'viewModel' => 'CourierSpecificsLengthColumnView',
                    'class' => 'length-col',
                    'sortable' => false,
                    'order' => 110,
                ],
            ],
            'CourierSpecificsInsuranceColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Insurance?'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsInsuranceColumn' => [
                'parameters' => [
                    'column' => 'insurance',
                    'viewModel' => 'CourierSpecificsInsuranceColumnView',
                    'class' => 'insurance-col',
                    'sortable' => false,
                    'order' => 120,
                    'defaultContent' => '',
                ],
            ],
            'CourierSpecificsInsuranceMonetaryColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Insurance<br/>Amount'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsInsuranceMonetaryColumn' => [
                'parameters' => [
                    'column' => 'insuranceMonetary',
                    'viewModel' => 'CourierSpecificsInsuranceMonetaryColumnView',
                    'class' => 'insuranceMonetary-col',
                    'sortable' => false,
                    'order' => 130,
                ],
            ],
            'CourierSpecificsInsuranceOptionsColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Insurance'],
                    // Note: this is NOT using the standard template but a bespoke one that loads up some JS
                    'template' => 'orders/courier/specifics/columns/insuranceOptions.phtml',
                ],
            ],
            'CourierSpecificsInsuranceOptionsColumn' => [
                'parameters' => [
                    'column' => 'insuranceOptions',
                    'viewModel' => 'CourierSpecificsInsuranceOptionsColumnView',
                    'class' => 'insuranceOptions-col',
                    'sortable' => false,
                    'order' => 140,
                ],
            ],
            'CourierSpecificsSignatureColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Signature?'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsSignatureColumn' => [
                'parameters' => [
                    'column' => 'signature',
                    'viewModel' => 'CourierSpecificsSignatureColumnView',
                    'class' => 'signature-col',
                    'sortable' => false,
                    'order' => 140,
                    'defaultContent' => '',
                ],
            ],
            'CourierSpecificsDeliveryInstructionsColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Delivery<br/>Instructions'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsDeliveryInstructionsColumn' => [
                'parameters' => [
                    'column' => 'deliveryInstructions',
                    'viewModel' => 'CourierSpecificsDeliveryInstructionsColumnView',
                    'class' => 'deliveryInstructions-col',
                    'sortable' => false,
                    'order' => 150,
                    'width' => '120px',
                ],
            ],
            'CourierSpecificsCostColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Cost'],
                    // Note: this is NOT using the standard template but a bespoke one that loads up some JS
                    'template' => 'orders/courier/specifics/columns/cost.phtml',
                ],
            ],
            'CourierSpecificsCostColumn' => [
                'parameters' => [
                    'column' => 'cost',
                    'viewModel' => 'CourierSpecificsCostColumnView',
                    'class' => 'cost-col',
                    'sortable' => false,
                    'order' => 145,
                    'defaultContent' => '',
                ],
            ],
            'CourierSpecificsItemParcelAssignmentColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Item(s) per<br />Parcel'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsItemParcelAssignmentColumn' => [
                'parameters' => [
                    'column' => 'itemParcelAssignment',
                    'viewModel' => 'CourierSpecificsItemParcelAssignmentColumnView',
                    'class' => 'itemParcelAssignment-col',
                    'sortable' => false,
                    'order' => 115,
                    'width' => '100px',
                ],
            ],
            'CourierSpecificsPackageTypeColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Package Type'],
                    // Note: this is NOT using the standard template but a bespoke one that loads up some JS
                    'template' => 'orders/courier/specifics/columns/packageType.phtml',
                ],
            ],
            'CourierSpecificsPackageTypeColumn' => [
                'parameters' => [
                    'column' => 'packageType',
                    'viewModel' => 'CourierSpecificsPackageTypeColumnView',
                    'class' => 'package-type-col',
                    'sortable' => false,
                    'order' => 116,
                ],
            ],
            'CourierSpecificsAddOnsColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Add-ons'],
                    // Note: this is NOT using the standard template but a bespoke one that loads up some JS
                    'template' => 'orders/courier/specifics/columns/addOns.phtml',
                ],
            ],
            'CourierSpecificsAddOnsColumn' => [
                'parameters' => [
                    'column' => 'addOns',
                    'viewModel' => 'CourierSpecificsAddOnsColumnView',
                    'class' => 'add-ons-col',
                    'sortable' => false,
                    'order' => 118,
                ],
            ],
            'CourierSpecificsDeliveryExperienceColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Delivery<br />Experience'],
                    // Note: this is NOT using the standard template but a bespoke one that loads up some JS
                    'template' => 'orders/courier/specifics/columns/deliveryExperience.phtml',
                ],
            ],
            'CourierSpecificsDeliveryExperienceColumn' => [
                'parameters' => [
                    'column' => 'deliveryExperience',
                    'viewModel' => 'CourierSpecificsDeliveryExperienceColumnView',
                    'class' => 'experience-col',
                    'sortable' => false,
                    'order' => 35,
                ],
            ],
            'CourierSpecificsCourierPickupColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Courier will<br />pick up?'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsCourierPickupColumn' => [
                'parameters' => [
                    'column' => 'courierPickup',
                    'viewModel' => 'CourierSpecificsCourierPickupColumnView',
                    'class' => 'courier-pickup-col',
                    'sortable' => false,
                    'order' => 37,
                ],
            ],
            'CourierSpecificsSaturdayColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Saturday?'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsSaturdayColumn' => [
                'parameters' => [
                    'column' => 'saturday',
                    'viewModel' => 'CourierSpecificsSaturdayColumnView',
                    'class' => 'saturday-col',
                    'sortable' => false,
                    'order' => 145,
                    'defaultContent' => '',
                ],
            ],
            'CourierSpecificsHarmonisedSystemCodeColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'HS Code'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsHarmonisedSystemCodeColumn' => [
                'parameters' => [
                    'column' => 'harmonisedSystemCode',
                    'viewModel' => 'CourierSpecificsHarmonisedSystemCodeColumnView',
                    'class' => 'harmonisedSystemCode-col',
                    'sortable' => false,
                    'order' => 115,
                    'width' => '50px',
                ],
            ],
            'CourierSpecificsHarmonisedSystemCodeDescriptionColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'HS Code Description'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsHarmonisedSystemCodeDescriptionColumn' => [
                'parameters' => [
                    'column' => 'harmonisedSystemCodeDescription',
                    'viewModel' => 'CourierSpecificsHarmonisedSystemCodeDescriptionColumnView',
                    'class' => 'harmonisedSystemCodeDescription-col',
                    'sortable' => false,
                    'order' => 117,
                    'width' => '50px',
                ],
            ],
            'CourierSpecificsCountryOfOriginColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Country of Origin'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsCountryOfOriginColumn' => [
                'parameters' => [
                    'column' => 'countryOfOrigin',
                    'viewModel' => 'CourierSpecificsCountryOfOriginColumnView',
                    'class' => 'countryOfOrigin-col',
                    'sortable' => false,
                    'order' => 119,
                    'width' => '30px',
                ],
            ],
            'CourierSpecificsEoriNumberColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'EORI Number'],
                    // Note: this is NOT using the standard template but a bespoke one that loads up some JS
                    'template' => 'orders/courier/specifics/columns/eoriNumber.phtml',
                ],
            ],
            'CourierSpecificsEoriNumberColumn' => [
                'parameters' => [
                    'column' => 'eoriNumber',
                    'viewModel' => 'CourierSpecificsEoriNumberColumnView',
                    'class' => 'eoriNumber-col',
                    'sortable' => false,
                    'order' => 123,
                    'width' => '30px',
                ],
            ],
            'CourierSpecificsTermsOfDeliveryColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Terms of Delivery'],
                    'template' => 'value.phtml',
                ],
            ],
            'CourierSpecificsTermsOfDeliveryColumn' => [
                'parameters' => [
                    'column' => 'termsOfDelivery',
                    'viewModel' => 'CourierSpecificsTermsOfDeliveryColumnView',
                    'class' => 'termsOfDelivery-col',
                    'sortable' => false,
                    'order' => 121,
                    'width' => '45px',
                ],
            ],
        ],
    ],
    'navigation' => array(
        'application-navigation' => array(
            'orders' => array(
                'label'  => 'Orders',
                'sprite' => 'sprite-orders-18-white',
                'order'  => 5,
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . '/orders',
                'pages'  => [
                    'createNewOrder' => [
                        'id'    => 'createNewOrder',
                        'label' => 'Create New Order',
                        'uri'   => 'https://' . $_SERVER['HTTP_HOST'] . implode(
                                '',
                                [
                                    Controller\OrdersController::ROUTE_INDEX_URL,
                                    ManualOrderController::ROUTE_INDEX_URL
                                ]
                            )
                    ]
                ]
            )
        )
    ),
];
