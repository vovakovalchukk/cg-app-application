<?php
namespace Products;

use CG\Account\Client\Storage\Api as AccountStorageApi;
use CG\Amazon\ListingImport as AmazonListingImport;
use CG\Channel\Listing\Download\Service as ChannelListingDownloadService;
use CG\Ebay\Listing\Creator as EbayListingCreator;
use CG\Image\Service as ImageService;
use CG\Image\Storage\Api as ImageApiStorage;
use CG\Listing\Service as ListingService;
use CG\Listing\Storage\Api as ListingApiStorage;
use CG\Listing\Unimported\Service as UnimportedListingService;
use CG\Listing\Unimported\Storage\Api as UnimportedListingApiStorage;
use CG\Product\Client\Service as ProductService;
use CG\Product\Link\Storage\Api as ProductLinkApiStorage;
use CG\Product\Link\StorageInterface as ProductLinkStorageInterface;
use CG\Product\LinkNode\Storage\Api as ProductLinkNodeApiStorage;
use CG\Product\LinkNode\StorageInterface as ProductLinkNodeStorageInterface;
use CG\Product\Storage\Api as ProductApiStorage;
use CG\Stock\Location\Service as LocationService;
use CG\Stock\Location\Storage\Api as LocationApiStorage;
use CG\Stock\Service as StockService;
use CG\Stock\Storage\Api as StockApiStorage;
use CG_UI\View\DataTable;
use Products\Controller\CreateListings\JsonController as CreateListingsJsonController;
use Products\Controller\CreateMultipleListings\JsonController as CreateMultipleListingsJsonController;
use Products\Controller\LinksJsonController;
use Products\Controller\ListingsController;
use Products\Controller\ListingsJsonController;
use Products\Controller\ProductsController;
use Products\Controller\ProductsJsonController;
use Products\Controller\PurchaseOrdersController;
use Products\Controller\PurchaseOrdersJsonController;
use Products\Controller\StockLogController;
use Products\Controller\StockLogJsonController;
use Products\Listing\Channel\Amazon\Service as ListingAmazonService;
use Products\Listing\Channel\Ebay\Service as ListingEbayService;
use Products\Product\Service as ModuleProductService;
use Products\Csv\Stock\ProgressStorage as StockCsvProgressStorage;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;
use Zend\View\Model\ViewModel;

return [
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => ProductsController::ROUTE_INDEX_URL,
                    'defaults' => [
                        'controller' => ProductsController::class,
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'sidebar' => false
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    ProductsJsonController::ROUTE_AJAX => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/ajax',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'ajax'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_STOCK_FETCH => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/stock/ajax/:productSku',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'stockFetch'
                            ]
                        ]
                    ],
                    ProductsJsonController::ROUTE_AJAX_TAX_RATE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/taxRate',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'saveProductTaxRate'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_NEW_NAME => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/name',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'saveProductName'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_STOCK_MODE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stockMode',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'saveProductStockMode'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_STOCK_LEVEL => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stockLevel',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'saveProductStockLevel'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_STOCK_UPDATE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stock/update',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'stockUpdate'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_STOCK_INC_PURCHASE_ORDERS => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/includePurchaseOrders',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'saveProductStockIncludePurchaseOrders'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_STOCK_CSV_EXPORT => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stock/csv/export',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'stockCsvExport'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            ProductsJsonController::ROUTE_STOCK_CSV_EXPORT_CHECK => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/check',
                                    'defaults' => [
                                        'action' => 'stockCsvExportCheck'
                                    ]
                                ],
                            ],
                            ProductsJsonController::ROUTE_STOCK_CSV_EXPORT_PROGRESS => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/progress',
                                    'defaults' => [
                                        'action' => 'stockCsvExportProgress'
                                    ]
                                ],
                            ],
                        ]
                    ],
                    ProductsJsonController::ROUTE_STOCK_CSV_IMPORT => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stock/csv/import',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'stockCsvImport'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_PRODUCT_LINK_CSV_EXPORT => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/link/csv/export',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'linkCsvExport'
                            ]
                        ],
                        'may_terminate' => true
                    ],
                    ProductsJsonController::ROUTE_DETAILS_UPDATE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/details/update',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'detailsUpdate'
                            ]
                        ],
                    ],
                    StockLogController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stockLog',
                            'defaults' => [
                                'controller' => StockLogController::class,
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            StockLogController::ROUTE_PRODUCT_LOGS => [
                                'type' => Segment::class,
                                'priority' => -100,
                                'options' => [
                                    'route' => '/:productId',
                                    'constraints' => [
                                        'productId' => '[0-9]+'
                                    ],
                                    'defaults' => [
                                        'action' => 'index',
                                        'sidebar' => false,
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    StockLogJsonController::ROUTE_AJAX => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/ajax',
                                            'defaults' => [
                                                'controller' => StockLogJsonController::class,
                                                'action' => 'ajax'
                                            ]
                                        ],
                                    ],

                                ]
                            ],
                            StockLogJsonController::ROUTE_UPDATE_COLUMNS => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/updateColumns',
                                    'defaults' => [
                                        'controller' => StockLogJsonController::class,
                                        'action' => 'updateColumns'
                                    ]
                                ],
                            ],
                        ]
                    ],

                    LinksJsonController::ROUTE_AJAX => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/links/ajax',
                            'defaults' => [
                                'controller' => LinksJsonController::class,
                                'action' => 'ajax'
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                    LinksJsonController::ROUTE_SAVE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/links/save',
                            'defaults' => [
                                'controller' => LinksJsonController::class,
                                'action' => 'save'
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                    LinksJsonController::ROUTE_REMOVE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/links/remove',
                            'defaults' => [
                                'controller' => LinksJsonController::class,
                                'action' => 'remove'
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                    ListingsJsonController::ROUTE_CREATE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/listing/submit',
                            'defaults' => [
                                'controller' => ListingsJsonController::class,
                                'action' => 'create'
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                    ListingsController::ROUTE_INDEX => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => ListingsController::ROUTE_INDEX_URL,
                            'defaults' => [
                                'controller' => ListingsController::class,
                                'action' => 'index'
                            ]
                        ],
                        'child_routes' => [
                            ListingsJsonController::ROUTE_AJAX => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/ajax',
                                    'defaults' => [
                                        'controller' => ListingsJsonController::class,
                                        'action' => 'ajax'
                                    ]
                                ],
                            ],
                            ListingsJsonController::ROUTE_HIDE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/hide',
                                    'defaults' => [
                                        'controller' => ListingsJsonController::class,
                                        'action' => 'hide'
                                    ]
                                ],
                            ],
                            ListingsJsonController::ROUTE_REFRESH => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/refresh',
                                    'defaults' => [
                                        'controller' => ListingsJsonController::class,
                                        'action' => 'refresh'
                                    ]
                                ],
                            ],
                            ListingsJsonController::ROUTE_REFRESH_DETAILS => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/refreshDetails',
                                    'defaults' => [
                                        'controller' => ListingsJsonController::class,
                                        'action' => 'refreshDetails'
                                    ]
                                ],
                            ],
                            ListingsJsonController::ROUTE_IMPORT => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/import',
                                    'defaults' => [
                                        'controller' => ListingsJsonController::class,
                                        'action' => 'import'
                                    ]
                                ],
                            ],
                            ListingsJsonController::ROUTE_IMPORT_ALL_FILTERED => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/importAllFiltered',
                                    'defaults' => [
                                        'controller' => ListingsJsonController::class,
                                        'action' => 'importAllFiltered'
                                    ]
                                ],
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    ProductsJsonController::ROUTE_DELETE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/delete',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'delete'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            ProductsJsonController::ROUTE_DELETE_CHECK => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/check',
                                    'defaults' => [
                                        'controller' => ProductsJsonController::class,
                                        'action' => 'deleteCheck'
                                    ]
                                ]
                            ],
                            ProductsJsonController::ROUTE_DELETE_PROGRESS => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/progress',
                                    'defaults' => [
                                        'controller' => ProductsJsonController::class,
                                        'action' => 'deleteProgress'
                                    ]
                                ]
                            ],
                        ]
                    ],
                    PurchaseOrdersController::ROUTE_INDEX => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/purchaseOrders',
                            'defaults' => [
                                'controller' => PurchaseOrdersController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            PurchaseOrdersJsonController::ROUTE_LIST => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'controller' => PurchaseOrdersJsonController::class,
                                        'action' => 'list'
                                    ]
                                ]
                            ],
                            PurchaseOrdersJsonController::ROUTE_COMPLETE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/complete',
                                    'defaults' => [
                                        'controller' => PurchaseOrdersJsonController::class,
                                        'action' => 'complete'
                                    ]
                                ]
                            ],
                            PurchaseOrdersJsonController::ROUTE_DOWNLOAD => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/download',
                                    'defaults' => [
                                        'controller' => PurchaseOrdersJsonController::class,
                                        'action' => 'download'
                                    ]
                                ]
                            ],
                            PurchaseOrdersJsonController::ROUTE_DELETE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'controller' => PurchaseOrdersJsonController::class,
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            PurchaseOrdersJsonController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'controller' => PurchaseOrdersJsonController::class,
                                        'action' => 'save'
                                    ]
                                ]
                            ],
                            PurchaseOrdersJsonController::ROUTE_CREATE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/create',
                                    'defaults' => [
                                        'controller' => PurchaseOrdersJsonController::class,
                                        'action' => 'create'
                                    ]
                                ]
                            ],
                        ],
                    ],
                    CreateListingsJsonController::ROUTE_CREATE_LISTINGS => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/create-listings/:accountId',
                        ],
                        'constraints' => [
                            'accountId' => '[0-9]+'
                        ],
                        'child_routes' => [
                            CreateListingsJsonController::ROUTE_DEFAULT_SETTINGS => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/default-settings',
                                    'defaults' => [
                                        'controller' => CreateListingsJsonController::class,
                                        'action' => 'defaultSettingsAjax'
                                    ]
                                ]
                            ],
                            CreateListingsJsonController::ROUTE_ACCOUNT_SPECIFIC_FIELD_VALUES => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/channel-specific-field-values',
                                    'defaults' => [
                                        'controller' => CreateListingsJsonController::class,
                                        'action' => 'channelSpecificFieldValues'
                                    ]
                                ]
                            ],
                            CreateListingsJsonController::ROUTE_REFRESH_CATEGORIES => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/refresh-categories',
                                    'defaults' => [
                                        'controller' => CreateListingsJsonController::class,
                                        'action' => 'refreshCategories'
                                    ],
                                ],
                            ],
                            CreateListingsJsonController::ROUTE_CATEGORY_DEPENDENT_FIELD_VALUES => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/category-dependent-field-values/:categoryId',
                                    'defaults' => [
                                        'controller' => CreateListingsJsonController::class,
                                        'action' => 'categoryDependentFieldValues'
                                    ],
                                ],
                                'constraints' => [
                                    'categoryId' => '[0-9]+'
                                ]
                            ],
                            CreateListingsJsonController::ROUTE_CATEGORY_CHILDREN => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/category-children/:categoryId',
                                    'defaults' => [
                                        'controller' => CreateListingsJsonController::class,
                                        'action' => 'categoryChildren'
                                    ],
                                ],
                                'constraints' => [
                                    'categoryId' => '[0-9]+'
                                ]
                            ],
                            CreateListingsJsonController::ROUTE_REFRESH_ACCOUNT_POLICIES => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/refresh-account-policies',
                                    'defaults' => [
                                        'controller' => CreateListingsJsonController::class,
                                        'action' => 'refreshAccountPolicies'
                                    ],
                                ],
                            ],
                            CreateListingsJsonController::ROUTE_PRODUCT_SEARCH => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/search',
                                    'defaults' => [
                                        'controller' => CreateListingsJsonController::class,
                                        'action' => 'search'
                                    ],
                                ],
                            ],
                        ]
                    ],
                    CreateListingsJsonController::ROUTE_CATEGORY_TEMPLATE_DEPENDENT_FIELD_VALUES => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/create-listings/category-template-dependent-field-values',
                            'defaults' => [
                                'controller' => CreateListingsJsonController::class,
                                'action' => 'categoryTemplateDependentFieldValues',
                            ],
                        ],
                    ],
                    CreateMultipleListingsJsonController::ROUTE_SUBMIT_MULTIPLE_LISTINGS => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/listing/submitMultiple',
                            'defaults' => [
                                'controller' => CreateMultipleListingsJsonController::class,
                                'action' => 'submitMultiple',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            CreateMultipleListingsJsonController::ROUTE_SUBMIT_MULTIPLE_LISTINGS_PROGRESS => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/progress/:key',
                                    'defaults' => [
                                        'action' => 'submitMultipleProgress'
                                    ],
                                ],
                            ],
                        ],
                    ],
                    ProductsJsonController::ROUTE_IMAGE_UPLOAD => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/create/imageUpload',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'imageUpload'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_CREATE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/create/save',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'create'
                            ]
                        ],
                    ],
                ]
            ]
        ]
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy'
        ],
        'template_path_stack' => [
            __NAMESPACE__ => dirname(__DIR__) . '/view',
            PROJECT_ROOT . '/public' . Module::PUBLIC_FOLDER . 'template',
        ]
    ],
    'di' => [
        'instance' => [
            'aliases' => [
                'ListingList' => DataTable::class,
                'ListingListSettings' => DataTable\Settings::class,
                'ListingCheckboxColumnView' => ViewModel::class,
                'ListingCheckboxColumn' => DataTable\Column::class,
                'ListingCheckboxCheckAll' => DataTable\CheckAll::class,
                'ListingChannelColumnView' => ViewModel::class,
                'ListingChannelColumn' => DataTable\Column::class,
                'ListingAccountColumnView' => ViewModel::class,
                'ListingAccountColumn' => DataTable\Column::class,
                'ListingMarketplaceColumnView' => ViewModel::class,
                'ListingMarketplaceColumn' => DataTable\Column::class,
                'ListingSkuColumnView' => ViewModel::class,
                'ListingSkuColumn' => DataTable\Column::class,
                'ListingImageColumnView' => ViewModel::class,
                'ListingImageColumn' => DataTable\Column::class,
                'ListingTitleColumnView' => ViewModel::class,
                'ListingTitleColumn' => DataTable\Column::class,
                'ListingFoundColumnView' => ViewModel::class,
                'ListingFoundColumn' => DataTable\Column::class,
                'ListingStatusColumnView' => ViewModel::class,
                'ListingStatusColumn' => DataTable\Column::class,

                'StockLogTable' => DataTable::class,
                'StockLogTableSettings' => DataTable\Settings::class,
                'StockLogIdColumnView' => ViewModel::class,
                'StockLogIdColumn' => DataTable\Column::class,
                'StockLogDateTimeColumnView' => ViewModel::class,
                'StockLogDateTimeColumn' => DataTable\Column::class,
                'StockLogItidColumnView' => ViewModel::class,
                'StockLogItidColumn' => DataTable\Column::class,
                'StockLogSkuColumnView' => ViewModel::class,
                'StockLogSkuColumn' => DataTable\Column::class,
                'StockLogStidColumnView' => ViewModel::class,
                'StockLogStidColumn' => DataTable\Column::class,
                'StockLogActionColumnView' => ViewModel::class,
                'StockLogActionColumn' => DataTable\Column::class,
                'StockLogAccountColumnView' => ViewModel::class,
                'StockLogAccountColumn' => DataTable\Column::class,
                'StockLogOrderColumnView' => ViewModel::class,
                'StockLogOrderColumn' => DataTable\Column::class,
                'StockLogListingIdColumnView' => ViewModel::class,
                'StockLogListingIdColumn' => DataTable\Column::class,
                'StockLogProductIdColumnView' => ViewModel::class,
                'StockLogProductIdColumn' => DataTable\Column::class,
                'StockLogStockManagementColumnView' => ViewModel::class,
                'StockLogStockManagementColumn' => DataTable\Column::class,
                'StockLogStatusColumnView' => ViewModel::class,
                'StockLogStatusColumn' => DataTable\Column::class,
                'StockLogStockIdColumnView' => ViewModel::class,
                'StockLogStockIdColumn' => DataTable\Column::class,
                'StockLogLocationIdColumnView' => ViewModel::class,
                'StockLogLocationIdColumn' => DataTable\Column::class,
                'StockLogAllocatedQtyColumnView' => ViewModel::class,
                'StockLogAllocatedQtyColumn' => DataTable\Column::class,
                'StockLogOnHandQtyColumnView' => ViewModel::class,
                'StockLogOnHandQtyColumn' => DataTable\Column::class,
                'StockLogAvailableQtyColumnView' => ViewModel::class,
                'StockLogAvailableQtyColumn' => DataTable\Column::class,
                'StockLogOnPurchaseOrderQtyColumnView' => ViewModel::class,
                'StockLogOnPurchaseOrderQtyColumn' => DataTable\Column::class,
                'StockLogOptionsColumnView' => ViewModel::class,
                'StockLogOptionsColumn' => DataTable\Column::class,
            ],
            ChannelListingDownloadService::class => [
                'parameters' => [
                    'accountStorage' => AccountStorageApi::class
                ]
            ],
            ListingsController::class => [
                'parameters' => [
                    'listingList' => 'ListingList'
                ]
            ],
            ProductService::class => [
                'parameters' => [
                    'repository' => ProductApiStorage::class,
                    'listingStorage' => ListingService::class,
                    'imageStorage' => ImageService::class
                ]
            ],
            ProductApiStorage::class => [
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
            UnimportedListingService::class => [
                'parameter' => [
                    'repository' => UnimportedListingApiStorage::class,
                    'imageStorage' => ImageService::class
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
            UnimportedListingApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
            AmazonListingImport::class => [
                'parameter' => [
                    'cryptor' => 'amazon_cryptor'
                ]
            ],
            'preference' => [
                ProductLinkStorageInterface::class => ProductLinkApiStorage::class,
                ProductLinkNodeStorageInterface::class => ProductLinkNodeApiStorage::class,
            ],
            ProductLinkApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ],
            ],
            ProductLinkNodeApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ],
            ],
            'ListingList' => [
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
                        ['column' => 'ListingCheckboxColumn'],
                        ['column' => 'ListingChannelColumn'],
                        ['column' => 'ListingAccountColumn'],
                        ['column' => 'ListingMarketplaceColumn'],
                        ['column' => 'ListingSkuColumn'],
                        ['column' => 'ListingImageColumn'],
                        ['column' => 'ListingTitleColumn'],
                        ['column' => 'ListingFoundColumn'],
                        ['column' => 'ListingStatusColumn']
                    ],
                    'setVariable' => [
                        ['name' => 'settings', 'value' => 'ListingListSettings']
                    ],
                ],
            ],
            'ListingCheckboxColumnView' => [
                'parameters' => [
                    'template' => 'orders/orders/table/header/checkbox.phtml',
                ],
            ],
            'ListingCheckboxColumn' => [
                'parameters' => [
                    'column' => 'id',
                    'viewModel' => 'ListingCheckboxColumnView',
                    'class' => 'checkbox',
                    'sortable' => false,
                    'hideable' => false,
                    'checkAll' => 'ListingCheckboxCheckAll'
                ],
            ],
            'ListingCheckboxCheckAll' => [
                'parameters' => [
                    'checkboxes' => '.checkbox-id',
                    'sortable' => false,
                ],
            ],
            'ListingChannelColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Channel'],
                    'template' => 'value.phtml',
                ],
            ],
            'ListingChannelColumn' => [
                'parameters' => [
                    'column' => 'channel',
                    'viewModel' => 'ListingChannelColumnView',
                    'class' => 'channel-col',
                    'sortable' => false,
                ],
            ],
            'ListingAccountColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Account'],
                    'template' => 'value.phtml',
                ],
            ],
            'ListingAccountColumn' => [
                'parameters' => [
                    'column' => 'accountId',
                    'viewModel' => 'ListingAccountColumnView',
                    'class' => 'account-col',
                    'sortable' => false,
                ],
            ],
            'ListingMarketplaceColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Site'],
                    'template' => 'value.phtml',
                ],
            ],
            'ListingMarketplaceColumn' => [
                'parameters' => [
                    'column' => 'marketplace',
                    'viewModel' => 'ListingMarketplaceColumnView',
                    'class' => 'marketplace-col',
                    'sortable' => false,
                ],
            ],
            'ListingSkuColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Sku'],
                    'template' => 'value.phtml',
                ],
            ],
            'ListingSkuColumn' => [
                'parameters' => [
                    'column' => 'sku',
                    'viewModel' => 'ListingSkuColumnView',
                    'class' => 'sku-col',
                    'sortable' => false,
                ],
            ],
            'ListingImageColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Image'],
                    'template' => 'value.phtml',
                ],
            ],
            'ListingImageColumn' => [
                'parameters' => [
                    'column' => 'image',
                    'viewModel' => 'ListingImageColumnView',
                    'class' => 'image-col',
                    'sortable' => false,
                ],
            ],
            'ListingTitleColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Title'],
                    'template' => 'value.phtml',
                ],
            ],
            'ListingTitleColumn' => [
                'parameters' => [
                    'column' => 'title',
                    'viewModel' => 'ListingTitleColumnView',
                    'class' => 'title-col',
                    'sortable' => false,
                ],
            ],
            'ListingFoundColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Found'],
                    'template' => 'value.phtml',
                ],
            ],
            'ListingFoundColumn' => [
                'parameters' => [
                    'column' => 'createdDate',
                    'viewModel' => 'ListingFoundColumnView',
                    'class' => 'found-col',
                    'sortable' => false,
                ],
            ],
            'ListingStatusColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Status'],
                    'template' => 'value.phtml',
                ],
            ],
            'ListingStatusColumn' => [
                'parameters' => [
                    'column' => 'status',
                    'viewModel' => 'ListingStatusColumnView',
                    'class' => 'status-col',
                    'sortable' => false,
                ],
            ],
            'ListingListSettings' => [
                'parameters' => [
                    'scrollHeightAuto' => false,
                    'footer' => true,
                    'pagination' => true,
                    'rowsPerPage' => 200,
                    'rowsPerPageList' => [50, 100, 200, 500],
                    'tableOptions' => 'rt<"table-footer" ilp>',
                    'language' => [
                      'sLengthMenu' => '<span class="show">Show</span> _MENU_'
                    ],
                ]
            ],

            'StockLogTable' => [
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
                        ['column' => 'StockLogIdColumn'],
                        ['column' => 'StockLogItidColumn'],
                        ['column' => 'StockLogAccountColumn'],
                        ['column' => 'StockLogSkuColumn'],
                        ['column' => 'StockLogOrderColumn'],
                        ['column' => 'StockLogListingIdColumn'],
                        ['column' => 'StockLogDateTimeColumn'],
                        ['column' => 'StockLogActionColumn'],
                        ['column' => 'StockLogStatusColumn'],
                        ['column' => 'StockLogOnHandQtyColumn'],
                        ['column' => 'StockLogAllocatedQtyColumn'],
                        ['column' => 'StockLogAvailableQtyColumn'],
                        ['column' => 'StockLogOnPurchaseOrderQtyColumn'],
                        ['column' => 'StockLogStockManagementColumn'],
                        ['column' => 'StockLogStidColumn'],
                        ['column' => 'StockLogProductIdColumn'],
                        ['column' => 'StockLogStockIdColumn'],
                        ['column' => 'StockLogLocationIdColumn'],
                        ['column' => 'StockLogOptionsColumn'],
                    ],
                    'setVariable' => [
                        ['name' => 'settings', 'value' => 'StockLogTableSettings']
                    ],
                ],
            ],
            'StockLogTableSettings' => [
                'parameters' => [
                    'language' => [
                        'sProcessing' => 'Loading logs',
                    ],
                ]
            ],
            'StockLogIdColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'ID'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogIdColumn' => [
                'parameters' => [
                    'column' => 'id',
                    'viewModel' => 'StockLogIdColumnView',
                    'class' => 'id-col',
                    'sortable' => false,
                ],
            ],
            'StockLogItidColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Log ID'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogItidColumn' => [
                'parameters' => [
                    'column' => 'itid',
                    'viewModel' => 'StockLogItidColumnView',
                    'class' => 'itid-col',
                    'sortable' => false,
                ],
            ],
            'StockLogAccountColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Account'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogAccountColumn' => [
                'parameters' => [
                    'column' => 'accountId',
                    'viewModel' => 'StockLogAccountColumnView',
                    'class' => 'account-col',
                    'sortable' => false,
                ],
            ],
            'StockLogSkuColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'SKU'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogSkuColumn' => [
                'parameters' => [
                    'column' => 'sku',
                    'viewModel' => 'StockLogSkuColumnView',
                    'class' => 'sku-col',
                    'sortable' => false,
                ],
            ],
            'StockLogOrderColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Order ID'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogOrderColumn' => [
                'parameters' => [
                    'column' => 'orderId',
                    'viewModel' => 'StockLogOrderColumnView',
                    'class' => 'order-col',
                    'sortable' => false,
                ],
            ],
            'StockLogDateTimeColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Date'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogDateTimeColumn' => [
                'parameters' => [
                    'column' => 'dateTime',
                    'viewModel' => 'StockLogDateTimeColumnView',
                    'class' => 'datetime-col',
                    'sortable' => false,
                ],
            ],
            'StockLogActionColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Action'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogActionColumn' => [
                'parameters' => [
                    'column' => 'action',
                    'viewModel' => 'StockLogActionColumnView',
                    'class' => 'action-col',
                    'sortable' => false,
                ],
            ],
            'StockLogStatusColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Status'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogStatusColumn' => [
                'parameters' => [
                    'column' => 'status',
                    'viewModel' => 'StockLogStatusColumnView',
                    'class' => 'status-col',
                    'sortable' => false,
                ],
            ],
            'StockLogOnHandQtyColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Total<br/>Stock'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogOnHandQtyColumn' => [
                'parameters' => [
                    'column' => 'onHandQty',
                    'viewModel' => 'StockLogOnHandQtyColumnView',
                    'class' => 'onhandqty-col',
                    'sortable' => false,
                ],
            ],
            'StockLogAllocatedQtyColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Awaiting<br/>Dispatch'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogAllocatedQtyColumn' => [
                'parameters' => [
                    'column' => 'allocatedQty',
                    'viewModel' => 'StockLogAllocatedQtyColumnView',
                    'class' => 'allocatedqty-col',
                    'sortable' => false,
                ],
            ],
            'StockLogAvailableQtyColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Available<br/>For Sale'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogAvailableQtyColumn' => [
                'parameters' => [
                    'column' => 'availableQty',
                    'viewModel' => 'StockLogAvailableQtyColumnView',
                    'class' => 'availableqty-col',
                    'sortable' => false,
                ],
            ],
            'StockLogOnPurchaseOrderQtyColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'On Purchase<br/>Order'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogOnPurchaseOrderQtyColumn' => [
                'parameters' => [
                    'column' => 'onPurchaseOrderQty',
                    'viewModel' => 'StockLogOnPurchaseOrderQtyColumnView',
                    'class' => 'onpurchaseorderqty-col',
                    'sortable' => false,
                ],
            ],
            'StockLogStockManagementColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Stock<br/>Mgmt'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogStockManagementColumn' => [
                'parameters' => [
                    'column' => 'stockManagement',
                    'viewModel' => 'StockLogStockManagementColumnView',
                    'class' => 'stockmanagement-col',
                    'sortable' => false,
                ],
            ],
            'StockLogStidColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Stid'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogStidColumn' => [
                'parameters' => [
                    'column' => 'stid',
                    'viewModel' => 'StockLogStidColumnView',
                    'class' => 'stid-col',
                    'sortable' => false,
                ],
            ],
            'StockLogListingIdColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Listing ID'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogListingIdColumn' => [
                'parameters' => [
                    'column' => 'listingId',
                    'viewModel' => 'StockLogListingIdColumnView',
                    'class' => 'listingid-col',
                    'sortable' => false,
                ],
            ],
            'StockLogProductIdColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Product ID'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogProductIdColumn' => [
                'parameters' => [
                    'column' => 'productId',
                    'viewModel' => 'StockLogProductIdColumnView',
                    'class' => 'productid-col',
                    'sortable' => false,
                ],
            ],
            'StockLogStockIdColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Stock ID'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogStockIdColumn' => [
                'parameters' => [
                    'column' => 'stockId',
                    'viewModel' => 'StockLogStockIdColumnView',
                    'class' => 'stockid-col',
                    'sortable' => false,
                ],
            ],
            'StockLogLocationIdColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Location ID'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockLogLocationIdColumn' => [
                'parameters' => [
                    'column' => 'locationId',
                    'viewModel' => 'StockLogLocationIdColumnView',
                    'class' => 'locationid-col',
                    'sortable' => false,
                ],
            ],
            'StockLogOptionsColumnView' => [
                'parameters' => [
                    'template' => 'table/column-picker.phtml',
                    'variables' => [
                        'persistUri' => '/products/stockLog/updateColumns',
                    ]
                ],
            ],
            'StockLogOptionsColumn' => [
                'parameters' => [
                    'order' => 9999,
                    'viewModel' => 'StockLogOptionsColumnView',
                    'class' => 'options',
                    'defaultContent' => '',
                    'sortable' => false,
                    'hideable' => false
                ],
            ],
            StockLogController::class => [
                'parameters' => [
                    'dataTable' => 'StockLogTable'
                ]
            ],

            ProductsController::class => [
                'parameters' => [
                    'accountStockSettingsTable' => 'StockSettingsAccountsTable', // defined in global.php
                ]
            ],
            ProductsJsonController::class => [
                'parameters' => [
                    'usageService' => 'order_count_usage_service',
                    'productsGearmanClient' => 'productGearmanClient'
                ]
            ],
            StockCsvProgressStorage::class => [
                'parameters' => [
                    'predis' => 'reliable_redis'
                ]
            ],
            ListingEbayService::class => [
                'parameters' => [
                    'cryptor' => 'ebay_cryptor'
                ]
            ],
            EbayListingCreator::class => [
                'parameters' => [
                    'cryptor' => 'ebay_cryptor'
                ]
            ],
            ListingAmazonService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ]
            ],
        ],
    ],
    'navigation' => array(
        'application-navigation' => [
            'products' => [
                'label'  => 'Products',
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . '/products',
                'sprite' => 'sprite-products-18-white',
                'order'  => 10,
                'pages'  => [
                    'importListings' => [
                        'id'    => 'importListings',
                        'label' => 'Import Listings',
                        'uri'   => 'https://' . $_SERVER['HTTP_HOST'] . implode(
                            '',
                            [
                                ProductsController::ROUTE_INDEX_URL,
                                ListingsController::ROUTE_INDEX_URL
                            ]
                        )
                    ],
                    'purchaseOrders' => [
                        'id'    => 'purchaseOrders',
                        'label' => 'Purchase Orders',
                        'uri'   => 'https://' . $_SERVER['HTTP_HOST'] . implode(
                                '',
                                [
                                    ProductsController::ROUTE_INDEX_URL,
                                    PurchaseOrdersController::ROUTE_INDEX_URL
                                ]
                            ),
                        'pre-render' => [
                            'diLoad' => [
                                'class' => ModuleProductService::class
                            ]
                        ],
                    ],
                ]
            ]
        ]
    ),
];
