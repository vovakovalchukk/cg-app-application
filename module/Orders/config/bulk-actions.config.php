<?php
use Orders\Order\BulkActions\Service;
use CG_UI\View\BulkActions;
use Zend\View\Model\ViewModel;
use Orders\Order\BulkActions\Action;
use Orders\Order\BulkActions\SubAction;

return [
    'di' => [
        'definition' => [
            'class' => [
                Action\Batch::class => [
                    'methods' => [
                        'addSubAction' => [
                            'subAction' => [
                                'required' => true,
                                'type' => BulkActions\SubAction::class
                            ],
                        ],
                    ],
                ],
                Action\Invoice::class => [
                    'methods' => [
                        'addSubAction' => [
                            'subAction' => [
                                'required' => true,
                                'type' => BulkActions\SubAction::class
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'instance' => [
            'aliases' => [
                'BulkActions' => BulkActions::class,
                'OrderDetailBulkActions' => BulkActions::class,
                'InvoiceBySkuBulkAction' => SubAction\InvoiceBySku::class,
                'InvoiceByTitleBulkAction' => BulkActions\SubAction::class,
                'RoyalMailBulkAction' => BulkActions\SubAction::class,
                'RemoveBatchBulkAction' => SubAction\Batch::class,
                'InvoiceJavascript' => ViewModel::class,
                'DispatchJavascript' => ViewModel::class,
                'TagJavascript' => ViewModel::class,
                'BatchJavascript' => ViewModel::class,
                'BatchRemoveJavascript' => ViewModel::class,
                'ArchiveJavascript' => ViewModel::class,
                'CancelJavascript' => ViewModel::class,
                'RefundJavascript' => ViewModel::class,
                'UrlDataViewInvoice' => ViewModel::class,
                'UrlDataViewInvoiceBySku' => ViewModel::class,
                'UrlDataViewDispatch' => ViewModel::class,
                'UrlDataViewTag' => ViewModel::class,
                'UrlDataViewArchive' => ViewModel::class,
                'UrlDataViewBatch' => ViewModel::class,
                'UrlDataViewBatchRemove' => ViewModel::class,
                'UrlDataViewCancelRefund' => ViewModel::class
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
                    'addAction' => [
                        ['action' => Action\Invoice::class],
                        ['action' => Action\Dispatch::class],
                        ['action' => Action\Tag::class],
                        ['action' => Action\Batch::class],
                        ['action' => Action\Archive::class],
                    ],
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
                    'addAction' => [
                        ['action' => Action\Invoice::class],
                        ['action' => Action\Dispatch::class],
                        ['action' => Action\Tag::class],
                        ['action' => Action\Cancel::class],
                        ['action' => Action\Refund::class],
                    ],
                ],
            ],
            Action\Invoice::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewInvoice',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'InvoiceJavascript',
                ],
                'injections' => [
                    'addSubAction' => [
                        ['subAction' => 'InvoiceBySkuBulkAction'],
                    ],
                ], 
            ],
            'InvoiceJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/invoice.js',
                ],
            ],
            Action\Dispatch::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewDispatch',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'DispatchJavascript',
                ]
            ],
            'DispatchJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/dispatch.js',
                ],
            ],
            'InvoiceBySkuBulkAction' => [
                'parameters' => [
                    'title' => 'by SKU',
                    'action' => 'invoices-sku',
                    'urlView' => 'UrlDataViewInvoiceBySku',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'InvoiceJavascript', 
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
                    'urlView' => 'UrlDataViewTag',
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
                    'addSubAction' => [
                        ['subAction' => 'RoyalMailBulkAction'],
                    ],
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
                    'urlView' => 'UrlDataViewBatch',
                    'elementData' => [
                        'datatable' => 'datatable'
                    ],
                    'javascript' => 'BatchJavascript',
                ],
                'injections' => [
                    'addSubAction' => [
                        ['subAction' => 'RemoveBatchBulkAction'],
                    ],
                ],
            ],
            'BatchJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/batch.js',
                ],
            ],
            'RemoveBatchBulkAction' => [
                'parameters' => [
                    'urlView' => 'UrlDataViewBatchRemove',
                    'title' => 'Remove',
                    'action' => 'remove',
                    'elementData' => [
                        'datatable' => 'datatable'
                    ],
                    'javascript' => 'BatchRemoveJavascript'
                ],
            ],
            'BatchRemoveJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/batchRemove.js',
                ],
            ],
            Action\Archive::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewArchive',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'ArchiveJavascript',
                ],
            ],
            'ArchiveJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/archive.js',
                ],
            ],
            Action\Cancel::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewCancelRefund',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'CancelJavascript',
                ],
            ],
            'CancelJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/cancel.js',
                ],
            ],
            Action\Refund::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewCancelRefund',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'RefundJavascript',
                ],
            ],
            'RefundJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/cancel.js',
                ],
            ],
            'UrlDataViewCancelRefund' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewInvoice' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewInvoiceBySku' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewTag' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewDispatch' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewArchive' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewBatch' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewBatchRemove' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
        ],
    ],
];