<?php
use Orders\Order\BulkActions\Service;
use CG_UI\View\BulkActions;
use Zend\View\Model\ViewModel;
use Orders\Order\BulkActions\TagActionModifier;

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
                    BulkActions\InvoiceAction::class,
                    BulkActions\DispatchAction::class,
                    BulkActions\TagAction::class,
                    BulkActions\DownloadAction::class,
                    BulkActions\CourierAction::class,
                    BulkActions\BatchAction::class,
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
                    BulkActions\TagAction::class,
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
            BulkActions\TagAction::class => [
                'parameters' => [
                    'javascript' => 'TagJavascript',
                    'elementData' => [
                        'datatable' => 'datatable',
                    ],
                ],
                'injections' => [
                    TagActionModifier::class
                ],
            ],
            'TagJavascript' => [
                'parameters' => [
                    'template' => 'orders/orders/bulk-actions/tag.js',
                ],
            ],
            TagActionModifier::class => [
                'parameters' => [
                    'router' => 'router',
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
            BulkActions\BatchAction::class => [
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