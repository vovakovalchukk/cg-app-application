<?php
use Products\Product\BulkActions\Service;
use CG_UI\View\ProductBulkActions as BulkActions;
use Zend\View\Model\ViewModel;
use Products\Product\BulkActions\Action;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'ProductListBulkActions' => BulkActions::class,
                'ProductDetailBulkActions' => BulkActions::class,
                'UrlDataViewDelete' => ViewModel::class,
                'UrlDataViewSearch' => ViewModel::class
            ],
            Service::class => [
                'parameters' => [
                    'listPageBulkActions' => 'ProductListBulkActions',
                    'detailPageBulkActions' => 'ProductDetailBulkActions',
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
                    Action\Search::class,
                    Action\Delete::class
                ],
            ],
            Action\Delete::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewInvoice',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ]
                ]
            ],
            'UrlDataViewDelete' => [
                'parameters' => [
                    'template' => 'products/products/bulk-actions/data-url',
                ],
            ],
            Action\Search::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewSearch',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ]
                ]
            ],
            'UrlDataViewSearch' => [
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
            ]
        ],
    ],
];