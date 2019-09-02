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
                Action\ToCsv::class => [
                    'methods' => [
                        'addSubAction' => [
                            'subAction' => [
                                'required' => true,
                                'type' => BulkActions\SubAction::class
                            ],
                        ],
                    ],
                ],
                Action\Courier::class => [
                    'methods' => [
                        'addSubAction' => [
                            'subAction' => [
                                'required' => true,
                                'type' => BulkActions\SubAction::class
                            ],
                        ],
                    ],
                ],
                Action\Archive::class => [
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
                'InvoiceByTitleBulkAction' => SubAction\InvoiceByTitle::class,
                'InvoiceEmailBulkAction' => SubAction\EmailInvoice::class,
                'ToCsvOrderDataOnlyBulkAction' => SubAction\ToCsvOrderDataOnly::class,
                'RoyalMailBulkAction' => BulkActions\SubAction::class,
                'RemoveBatchBulkAction' => SubAction\Batch::class,
                'CourierManifestAction' => SubAction\CourierManifest::class,
                'InvoiceJavascript' => ViewModel::class,
                'InvoiceEmailJavascript' => ViewModel::class,
                'DispatchJavascript' => ViewModel::class,
                'TagJavascript' => ViewModel::class,
                'BatchJavascript' => ViewModel::class,
                'BatchRemoveJavascript' => ViewModel::class,
                'ArchiveJavascript' => ViewModel::class,
                'CancelJavascript' => ViewModel::class,
                'PayJavascript' => ViewModel::class,
                'RefundJavascript' => ViewModel::class,
                'UnlinkJavascript' => ViewModel::class,
                'PickListJavascript' => ViewModel::class,
                'ToCsvJavascript' => ViewModel::class,
                'CourierJavascript' => ViewModel::class,
                'CourierManifestJavascript' => ViewModel::class,
                'UrlDataViewInvoice' => ViewModel::class,
                'UrlDataViewInvoiceBySku' => ViewModel::class,
                'UrlDataViewInvoiceByTitle' => ViewModel::class,
                'UrlDataViewEmailInvoice' => ViewModel::class,
                'UrlDataViewDispatch' => ViewModel::class,
                'UrlDataViewTag' => ViewModel::class,
                'UrlDataViewArchive' => ViewModel::class,
                'UrlDataViewUnArchive' => ViewModel::class,
                'UrlDataViewBatch' => ViewModel::class,
                'UrlDataViewBatchRemove' => ViewModel::class,
                'UrlDataViewPay' => ViewModel::class,
                'UrlDataViewCancelRefund' => ViewModel::class,
                'UrlDataViewUnlink' => ViewModel::class,
                'UrlDataViewPickList' => ViewModel::class,
                'UrlDataViewToCsv' => ViewModel::class,
                'UrlDataViewToCsvOrderDataOnly' => ViewModel::class,
                'UrlDataViewCourier' => ViewModel::class,
                'UrlDataViewCourierManifest' => ViewModel::class,
                'Invoice' => Action\Invoice::class
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
                        ['action' => Action\PickList::class],
                        ['action' => Action\Dispatch::class],
                        ['action' => Action\Courier::class],
                        ['action' => Action\ToCsv::class],
                        ['action' => Action\Tag::class],
                        ['action' => Action\Batch::class],
                        ['action' => Action\Archive::class]
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
                        ['action' => 'Invoice'],
                        ['action' => Action\Dispatch::class],
                        ['action' => Action\Courier::class],
                        ['action' => Action\Tag::class],
                        ['action' => Action\Cancel::class],
                        ['action' => Action\Pay::class],
                        ['action' => Action\Refund::class],
                        ['action' => Action\Unlink::class],
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
                        ['subAction' => 'InvoiceByTitleBulkAction'],
                        ['subAction' => 'InvoiceEmailBulkAction'],
                    ],
                ],
            ],
            'InvoiceJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/invoice.js',
                ],
            ],
            'Invoice' => [
                'parameters' => [
                    'urlView' => 'UrlDataViewInvoice',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                ],
                'injections' => [
                    'addSubAction' => [
                        ['subAction' => 'InvoiceEmailBulkAction'],
                    ],
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
            'PickListJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/pickList.js'
                ]
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
                    'action' => 'invoices-title',
                    'urlView' => 'UrlDataViewInvoiceByTitle',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'InvoiceJavascript',
                ],
            ],
            'InvoiceEmailBulkAction' => [
                'parameters' => [
                    'urlView' => 'UrlDataViewEmailInvoice',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'InvoiceEmailJavascript',
                ],
            ],
            'InvoiceEmailJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/email-invoice.js',
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
                        'message' => 'Archiving Orders',
                        'success' => 'Archived Successfully',
                        'error' => 'Failed to archived Orders',
                    ],
                    'javascript' => 'ArchiveJavascript',
                ],
                'injections' => [
                    'addSubAction' => [
                        ['subAction' => SubAction\UnArchive::class]
                    ]
                ],
            ],
            SubAction\UnArchive::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewUnArchive',
                    'elementData' => [
                        'datatable' => 'datatable',
                        'message' => 'Un-Archiving Orders',
                        'success' => 'Un-Archived Successfully',
                        'error' => 'Failed to un-archived Orders',
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
            Action\Pay::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewPay',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'PayJavascript',
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
            Action\Unlink::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewUnlink',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                    'javascript' => 'UnlinkJavascript',
                ],
            ],
            Action\PickList::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewPickList',
                    'elementData' => [
                        'datatable' => 'datatable'
                    ],
                    'javascript' => 'PickListJavascript'
                ]
            ],
            Action\ToCsv::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewToCsv',
                    'elementData' => [
                        'datatable' => 'datatable'
                    ],
                    'javascript' => 'ToCsvJavascript'
                ],
                'injections' => [
                    'addSubAction' => [
                        ['subAction' => 'ToCsvOrderDataOnlyBulkAction']
                    ]
                ]
            ],
            'ToCsvOrderDataOnlyBulkAction' => [
                'parameters' => [
                    'urlView' => 'UrlDataViewToCsvOrderDataOnly',
                    'elementData' => [
                        'datatable' => 'datatable'
                    ],
                    'javascript' => 'ToCsvJavascript'
                ],
            ],
            'ToCsvJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/toCsv.js'
                ]
            ],

            Action\Courier::class => [
                'parameters' => [
                    'urlView' => 'UrlDataViewCourier',
                    'elementData' => [
                        'datatable' => 'datatable'
                    ],
                    'javascript' => 'CourierJavascript',
                ],
                'injections' => [
                    'addSubAction' => [
                        ['subAction' => 'CourierManifestAction']
                    ]
                ]
            ],
            'UrlDataViewCourier' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'CourierJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/courier.js'
                ]
            ],
            'CourierManifestAction' => [
                'parameters' => [
                    'urlView' => 'UrlDataViewCourierManifest',
                    'elementData' => [
                        'datatable' => 'datatable'
                    ],
                    'javascript' => 'CourierManifestJavascript'
                ],
            ],
            'UrlDataViewCourierManifest' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'CourierManifestJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/courierManifest.js'
                ]
            ],
            'PayJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/pay.js',
                ],
            ],
            'UrlDataViewPay' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
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
            'UnlinkJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/unlink.js',
                ],
            ],
            'UrlDataViewUnlink' => [
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
            'UrlDataViewInvoiceByTitle' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ],
            ],
            'UrlDataViewEmailInvoice' => [
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
            'UrlDataViewUnArchive' => [
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
            'UrlDataViewPickList' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ]
            ],
            'UrlDataViewToCsv' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ]
            ],
            'UrlDataViewToCsvOrderDataOnly' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/data-url',
                ]
            ]
        ],
    ],
];
