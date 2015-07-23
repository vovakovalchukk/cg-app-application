<?php
use Products\Product\BulkActions\Service as ProductBulkActionsService;
use Products\Listing\BulkActions\Service as ListingBulkActionsService;
use CG_UI\View\BulkActions;
use Zend\View\Model\ViewModel;
use Products\Product\BulkActions\Action as ProductAction;
use Products\Listing\BulkActions\Action as ListingAction;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'ProductListBulkActions' => BulkActions::class,
                'ProductDetailBulkActions' => BulkActions::class,
                'ListingListBulkActions' => BulkActions::class,
                'ListingDetailBulkActions' => BulkActions::class,
                'DeleteJSViewModel' => ViewModel::class,
                'StockImportJSViewModel' => ViewModel::class,
                'HideJSViewModel' => ViewModel::class,
                'ImportJSViewModel' => ViewModel::class,
                'UrlDataViewSearch' => ViewModel::class,
                'UrlDataViewStockImport' => ViewModel::class
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
                        ['action' => ProductAction\Delete::class],
                        ['action' => ProductAction\StockExport::class],
                        ['action' => ProductAction\StockImport::class]
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
            'UrlDataViewStockImport' => [
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
