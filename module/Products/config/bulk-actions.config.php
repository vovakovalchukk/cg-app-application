<?php
use Products\Product\BulkActions\Service as ProductBulkActionsService;
use Products\Listing\BulkActions\Service as ListingBulkActionsService;
use CG_UI\View\BulkActions;
use Zend\View\Model\ViewModel;
use Products\Product\BulkActions\Action;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'ProductListBulkActions' => BulkActions::class,
                'ProductDetailBulkActions' => BulkActions::class,
                'ListingListBulkActions' => BulkActions::class,
                'ListingDetailBulkActions' => BulkActions::class,
                'DeleteJSViewModel' => ViewModel::class,
                'UrlDataViewSearch' => ViewModel::class
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
                        ['action' => Action\Delete::class]
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

                    ]
                ],
            ],
            Action\Delete::class => [
                'parameters' => [
                    'javascript' => 'DeleteJSViewModel'
                ]
            ],
            'DeleteJSViewModel' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/delete-js',
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