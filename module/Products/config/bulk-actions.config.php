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
                'DeleteJSViewModel' => ViewModel::class,
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
                    Action\Delete::class
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
            ]
        ],
    ],
];