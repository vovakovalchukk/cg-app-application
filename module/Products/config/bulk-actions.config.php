<?php
use Products\Product\BulkActions\Service as ProductBulkActionsService;
use Products\Listing\BulkActions\Service as ListingBulkActionsService;
use CG_UI\View\BulkActions;
use Zend\View\Model\ViewModel;
use Products\Product\BulkActions\Action as ProductAction;
use Products\Listing\BulkActions\Action as ListingAction;

return [
    'di' => [
        'definition' => [
            'class' => [
                ListingAction\Import::class => [
                    'methods' => [
                        'addSubAction' => [
                            'subAction' => [
                                'required' => true,
                                'type' => BulkActions\SubAction::class
                            ],
                        ],
                    ],
                ],
            ]
        ],
        'instance' => [
            'aliases' => [
                'ProductListBulkActions' => BulkActions::class,
                'ProductDetailBulkActions' => BulkActions::class,
                'ListingListBulkActions' => BulkActions::class,
                'ListingDetailBulkActions' => BulkActions::class,
                'DeleteJSViewModel' => ViewModel::class,
                'StockImportJSViewModel' => ViewModel::class,
                'StockExportJSViewModel' => ViewModel::class,
                'ProductLinkExportJSViewModel' => ViewModel::class,
                'ProductLinkImportJSViewModel' => ViewModel::class,
                'HideJSViewModel' => ViewModel::class,
                'ImportJSViewModel' => ViewModel::class,
                'ImportAllFilteredJSViewModel' => ViewModel::class,
                'UrlDataViewSearch' => ViewModel::class,
                'UrlDataViewStockImport' => ViewModel::class,
                'UrlDataViewStockExport' => ViewModel::class,
                'UrlDataViewProductLinkExport' => ViewModel::class,
                'UrlDataViewProductLinkImport' => ViewModel::class
            ],
            ProductBulkActionsService::class => [
                'parameters' => [
                    'listPageBulkActions' => 'ProductListBulkActions',
                    'detailPageBulkActions' => 'ProductDetailBulkActions',
                ],
            ],
            ListingBulkActionsService::class => [
                'parameters' => [
                    'listPageBulkActions' => 'ListingListBulkActions',
                    'detailPageBulkActions' => 'ListingDetailBulkActions',
                ],
            ],
            'ProductListBulkActions' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'bulk-actions',
                        'class' => ['fixed-scroll'],
                    ],
                ],
                'injections' => [
                    'addAction' => [
                        ['action' => ProductAction\StockExport::class],
                        ['action' => ProductAction\StockImport::class],
                        ['action' => ProductAction\ProductLinkExport::class],
                        ['action' => ProductAction\ProductLinkImport::class],
                    ]
                ],
            ],
            'ListingListBulkActions' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'bulk-actions',
                        'class' => ['fixed-scroll'],
                    ],
                ],
                'injections' => [
                    'addAction' => [
                        ['action' => ListingAction\Hide::class],
                        ['action' => ListingAction\Import::class]
                    ]
                ],
            ],
            ProductAction\Delete::class => [
                'parameters' => [
                    'javascript' => 'DeleteJSViewModel'
                ]
            ],
            'DeleteJSViewModel' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/delete-js',
                ],
            ],
            ProductAction\StockImport::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewStockImport',
                    'javascript' => 'StockImportJSViewModel'
                ]
            ],
            'StockImportJSViewModel' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/stock-import',
                ],
            ],
            ProductAction\StockExport::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewStockExport',
                    'javascript' => 'StockExportJSViewModel'
                ]
            ],
            'StockExportJSViewModel' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/stock-export',
                ],
            ],
            ProductAction\ProductLinkExport::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewProductLinkExport',
                    'javascript' => 'ProductLinkExportJSViewModel'
                ]
            ],
            'ProductLinkExportJSViewModel' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/product-link-export',
                ],
            ],
            ProductAction\ProductLinkImport::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewProductLinkImport',
                    'javascript' => 'ProductLinkImportJSViewModel'
                ]
            ],
            'ProductLinkImportJSViewModel' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/product-link-import',
                ],
            ],
            'UrlDataViewStockImport' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewStockExport' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewProductLinkExport' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewProductLinkImport' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/data-url',
                ],
            ],
            'ProductDetailBulkActions' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'bulk-actions',
                        'class' => ['bulk-actions-inline'],
                    ],
                ]
            ],
            ListingAction\Hide::class => [
                'parameters' => [
                    'javascript' => 'HideJSViewModel'
                ]
            ],
            ListingAction\Import::class => [
                'parameters' => [
                    'javascript' => 'ImportJSViewModel'
                ],
                'injections' => [
                    'addSubAction' => [
                        ['subAction' => ListingAction\ImportAllFiltered::class],
                    ],
                ],
            ],
            ListingAction\ImportAllFiltered::class => [
                'parameters' => [
                    'javascript' => 'ImportAllFilteredJSViewModel'
                ]
            ],
            'HideJSViewModel' => [
                'parameters' => [
                    'template' => 'products/listings/bulk-actions/hide-js',
                ],
            ],
            'ImportJSViewModel' => [
                'parameters' => [
                    'template' => 'products/listings/bulk-actions/import-js',
                ],
            ],
            'ImportAllFilteredJSViewModel' => [
                'parameters' => [
                    'template' => 'products/listings/bulk-actions/import-all-filtered-js',
                ],
            ],
            'ListingDetailBulkActions' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'bulk-actions',
                        'class' => ['bulk-actions-inline'],
                    ],
                ]
            ]
        ],
    ],
];
