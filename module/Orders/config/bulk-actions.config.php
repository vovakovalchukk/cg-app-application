<?php
use Orders\Order\BulkActions\Service;
use CG_UI\View\BulkActions;
use CG_UI\View\BulkActions\Action;
use CG_UI\View\BulkActions\PrintAction;
use CG_UI\View\BulkActions\DispatchAction;
use CG_UI\View\BulkActions\TagAction;

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
            ],
        ],
        'instance' => [
            'aliases' => [
                'BulkActions' => BulkActions::class,
                'OrderDetailBulkActions' => BulkActions::class,
                'CourierBulkAction' => Action::class,
                'AccountingBulkAction' => Action::class,
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
                    'CourierBulkAction',
                    'AccountingBulkAction',
                ],
            ],
            'CourierBulkAction' => [
                'parameters' => [
                    'icon' => 'courier',
                    'title' => 'Courier',
                    'action' => 'courier'
                ],
            ],
            'AccountingBulkAction' => [
                'parameters' => [
                    'icon' => 'accounting',
                    'title' => 'Accounting',
                    'action' => 'accounting'
                ],
            ],
        ],
    ],
];