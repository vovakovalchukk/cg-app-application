<?php
namespace Products;

use CG\Amazon\ListingImport as AmazonListingImport;
use CG\Image\Service as ImageService;
use CG\Image\Storage\Api as ImageApiStorage;
use CG\Listing\Service as ListingService;
use CG\Listing\Storage\Api as ListingApiStorage;
use CG\Listing\Unimported\Service as UnimportedListingService;
use CG\Listing\Unimported\Storage\Api as UnimportedListingApiStorage;
use CG\Product\Client\Service as ProductService;
use CG\Product\Storage\Api as ProductApiStorage;
use CG\Stock\Location\Service as LocationService;
use CG\Stock\Location\Storage\Api as LocationApiStorage;
use CG\Stock\Service as StockService;
use CG\Stock\Storage\Api as StockApiStorage;
use CG_UI\View\DataTable;
use Products\Controller;
use Products\Controller\AdminStockLogController;
use Products\Controller\ListingsController;
use Products\Controller\ListingsJsonController;
use Products\Controller\ProductsController;
use Products\Controller\ProductsJsonController;
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
                    ProductsJsonController::ROUTE_STOCK_CSV_EXPORT => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stock/csv/export',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'stockCsvExport'
                            ]
                        ],
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
                    AdminStockLogController::ROUTE_STOCKLOG => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/stockLog/:productId',
                            'constraints' => [
                                'productId' => '[0-9]+'
                            ],
                            'defaults' => [
                                'controller' => AdminStockLogController::class,
                                'action' => 'index',
                                'subHeader' => false,
                                'sidebar' => false,
                            ]
                        ],
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
                            ListingsJsonController::ROUTE_IMPORT => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/import',
                                    'defaults' => [
                                        'controller' => ListingsJsonController::class,
                                        'action' => 'import'
                                    ]
                                ],
                            ]
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
            ],
            ListingsController::class => [
                'parameters' => [
                    'listingList' => 'ListingList'
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
                    'class' => 'statuc-col',
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
            ProductsController::class => [
                'parameters' => [
                    'accountStockSettingsTable' => 'StockSettingsAccountsTable', // defined in global.php
                ]
            ],
            AdminStockLogController::class => [
                'parameters' => [
                    'sql' => 'cg_appReadSql',
                ],
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
                    ]
                ]
            ]
        ]
    ),
];
