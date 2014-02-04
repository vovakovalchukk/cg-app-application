<?php
use Orders\Order\BulkActions\Service;
use CG_UI\View\BulkActions;
use CG_UI\View\BulkActions\Action;
use CG_UI\View\BulkActions\SubAction;
use CG_UI\View\BulkActions\InvoiceAction;
use CG_UI\View\BulkActions\DispatchAction;
use CG_UI\View\BulkActions\TagAction;
use CG_UI\View\BulkActions\DownloadAction;
use CG_UI\View\BulkActions\CourierAction;
use CG_UI\View\BulkActions\BatchAction;
use CG_UI\View\BulkActions\ArchiveAction;
use CG_UI\View\BulkActions\PrintAction;
use CG_UI\View\BulkActions\AccountingAction;
use Zend\View\Model\ViewModel;

return [
    'di' => [
        'definition' => [
            'class' => [
                BulkActions::class => [
                    'methods' => [
                        'addAction' => [
                            'action' => [
                                'required' => true,
                                'type' => Action::class,
                            ],
                        ],
                    ],
                ],
                Action::class => [
                    'methods' => [
                        'addSubAction' => [
                            'subAction' => [
                                'required' => true,
                                'type' => SubAction::class
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
                'InvoiceBySkuBulkAction' => SubAction::class,
                'InvoiceByTitleBulkAction' => SubAction::class,
                'RoyalMailBulkAction' => SubAction::class,
                'RemoveBatchBulkAction' => SubAction::class,
                'TagJavascript' => ViewModel::class
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
                    InvoiceAction::class,
                    DispatchAction::class,
                    TagAction::class,
                    DownloadAction::class,
                    CourierAction::class,
                    BatchAction::class,
                    ArchiveAction::class,
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
                    PrintAction::class,
                    DispatchAction::class,
                    TagAction::class,
                    CourierAction::class,
                    AccountingAction::class,
                ],
            ],
            InvoiceAction::class => [
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
            TagAction::class => [
                'parameters' => [
                    'javascript' => 'TagJavascript',
                ],
            ],
            'TagJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/tag.js',
                ],
            ],
            CourierAction::class => [
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
            BatchAction::class => [
                'injections' => [
                    'RemoveBatchBulkAction',
                ],
            ],
            'RemoveBatchBulkAction' => [
                'parameters' => [
                    'title' => 'Remove',
                    'action' => 'remove-from-batch'
                ],
            ],
        ],
    ],
];