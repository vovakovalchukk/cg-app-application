<?php
use Orders\Order\BulkActions\Service;
use CG_UI\View\BulkActions;
use Zend\View\Model\ViewModel;
use Orders\Order\BulkActions\Action;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'BulkActions' => BulkActions::class,
                'OrderDetailBulkActions' => BulkActions::class,
                'InvoiceBySkuBulkAction' => BulkActions\SubAction::class,
                'InvoiceByTitleBulkAction' => BulkActions\SubAction::class,
                'RoyalMailBulkAction' => BulkActions\SubAction::class,
                'RemoveBatchBulkAction' => BulkActions\SubAction::class,
                'TagJavascript' => ViewModel::class,
                'UrlDataView' => ViewModel::class,
            ],
            Service::class => [
                'parameters' => [
                    'bulkActions' => 'BulkActions',
                    'orderBulkActions' => 'OrderDetailBulkActions'
                ],
            ],
            'BulkActions' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'bulk-actions',
                        'class' => ['fixed-scroll'],
                    ],
                ],
                'injections' => [
                    BulkActions\InvoiceAction::class,
                    BulkActions\DispatchAction::class,
                    Action\Tag::class,
                    BulkActions\DownloadAction::class,
                    BulkActions\CourierAction::class,
                    Action\Batch::class,
                    BulkActions\ArchiveAction::class,
                ],
            ],
            'OrderDetailBulkActions' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'bulk-actions',
                        'class' => ['bulk-actions-inline'],
                    ],
                ],
                'injections' => [
                    BulkActions\PrintAction::class,
                    BulkActions\DispatchAction::class,
                    Action\Tag::class,
                    BulkActions\CourierAction::class,
                    BulkActions\AccountingAction::class,
                ],
            ],
            BulkActions\InvoiceAction::class => [
                'injections' => [
                    'InvoiceBySkuBulkAction',
                    'InvoiceByTitleBulkAction',
                ],
            ],
            'InvoiceBySkuBulkAction' => [
                'parameters' => [
                    'title' => 'by SKU',
                    'action' => 'invoices-sku'
                ],
            ],
            'InvoiceByTitleBulkAction' => [
                'parameters' => [
                    'title' => 'by Title',
                    'action' => 'invoices-title'
                ],
            ],
            Action\Tag::class => [
                'parameters' => [
                    'urlView' => 'UrlDataView',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'TagJavascript',
                ],
            ],
            'TagJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/tag.js',
                ],
            ],
            BulkActions\CourierAction::class => [
                'injections' => [
                    'RoyalMailBulkAction',
                ],
            ],
            'RoyalMailBulkAction' => [
                'parameters' => [
                    'title' => 'Create Royal Mail CSV',
                    'action' => 'royal-mail-csv'
                ],
            ],
            Action\Batch::class => [
                'parameters' => [
                    'urlView' => 'UrlDataView',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'BatchJavascript',
                ],
                'injections' => [
                    'RemoveBatchBulkAction',
                ],
            ],
            'BatchJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/batch.js',
                ],
            ],
            'RemoveBatchBulkAction' => [
                'parameters' => [
                    'title' => 'Remove',
                    'action' => 'remove-from-batch'
                ],
            ],
            'UrlDataView' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
        ],
    ],
];