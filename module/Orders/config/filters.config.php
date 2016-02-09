<?php
use CG_UI\View\Filters\Service as FilterService;
use Orders\Order\CountryService;
use Orders\Order\CurrencyService;
use Orders\Order\TableService\OrdersTableTagColumns;
use Orders\Order\TableService\OrdersTableFulfilmentChannelColumns;
use Filters\Options\Channel;
use Filters\Options\Account;
use Orders\Order\Filter\Batch;
use Orders\Order\Filter\Shipping;
use Orders\Controller\OrdersController;
use CG\Order\Shared\Status;

return [
    'di' => [
        'instance' => [
            FilterService::class => [
                'parameters' => [
                    'config' => 'config',
                ],
            ],
        ]
    ],
    'filters' => [
        OrdersController::FILTER_TYPE => [
            'rows' => [
                [
                    'type' => 'Row',
                    'filters' => [
                        [
                            'filterType' => 'date-range',
                            'variables' => [
                                'name' => 'purchaseDate',
                                'time' => [
                                    'hours' => '23',
                                    'minutes' => '59'
                                ],
                                'options' => [
                                    [
                                        'title' => 'All Time'
                                    ],
                                    [
                                        'title' => 'Today',
                                        'from' => 'today',
                                        'to' => '23:59'
                                    ],
                                    [
                                        'title' => 'Last 7 days',
                                        'from' => '-7 days',
                                        'to' => '23:59'
                                    ],
                                    [
                                        'title' => 'Month to date',
                                        'from' => 'midnight first day of this month',
                                        'to' => '23:59'
                                    ],
                                    [
                                        'title' => 'Year to date',
                                        'from' => 'first day of January',
                                        'to' => '23:59'
                                    ],
                                    [
                                        'title' => 'The previous month',
                                        'from' => 'midnight first day of last month',
                                        'to' => '23:59 last day of last month',
                                    ]
                                ]
                            ]
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'variables' => [
                                'name' => 'status',
                                'title' => 'Status',
                                'id' => 'filter-status',
                                'searchField' => true,
                                'concatenate' => true,
                                'options' => [
                                    [
                                        'title' => ucwords(Status::AWAITING_PAYMENT),
                                        'value' => Status::AWAITING_PAYMENT
                                    ],
                                    [
                                        'title' => ucwords(Status::NEW_ORDER),
                                        'value' => Status::NEW_ORDER
                                    ],
                                    [
                                        'title' => ucwords(Status::DISPATCHING),
                                        'value' => Status::DISPATCHING
                                    ],
                                    [
                                        'title' => ucwords(Status::DISPATCHED),
                                        'value' => Status::DISPATCHED
                                    ],
                                    [
                                        'title' => ucwords(Status::CANCELLING),
                                        'value' => Status::CANCELLING
                                    ],
                                    [
                                        'title' => ucwords(Status::CANCELLED),
                                        'value' => Status::CANCELLED
                                    ],
                                    [
                                        'title' => ucwords(Status::REFUNDING),
                                        'value' => Status::REFUNDING
                                    ],
                                    [
                                        'title' => ucwords(Status::REFUNDED),
                                        'value' => Status::REFUNDED
                                    ],
                                ],
                            ],
                        ],
                        [
                            'filterType' => 'search',
                            'variables' => [
                                'name' => 'searchTerm',
                                'placeholder' => 'Search for...',
                                'class' => '',
                                'value' => ''
                            ],
                        ],
                        [
                            'filterType' => 'more',
                            'variables' => [
                                'id' => 'filter-more-button',
                                'searchField' => true,
                                'title' => 'More',
                                'class' => 'more',
                                'name' => 'more'
                            ],
                        ],
                        [
                            'filterType' => 'buttons',
                            'variables' => [
                                'name' => 'buttons',
                                'buttons' => [
                                    [
                                        'name' => 'apply-filters',
                                        'value' => 'Apply Filters',
                                        'action' => 'apply-filters',
                                    ],
                                    [
                                        'name' => 'clear-filters',
                                        'value' => 'Clear',
                                        'action' => 'clear-filters',
                                    ],
                                    [
                                        'name' => 'save-filters',
                                        'value' => 'Save',
                                        'action' => 'save-filters',
                                    ],
                                ],
                            ]
                        ],
                    ],
                ],
                [
                    'type' => 'Row',
                    'filters' => [
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => true,
                            'variables' => [
                                'name' => 'shippingAddressCountry',
                                'title' => 'Country',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => [
                                ]
                            ],
                            'optionsProvider' => CountryService::class,
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'name' => 'currencyCode',
                                'title' => 'Currency',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => CurrencyService::class,
                        ],
                        [
                            'filterType' => 'numberRange',
                            'visible' => true,
                            'variables' => [
                                'name' => 'total',
                                'title' => 'Total',
                                'isOptional' => true,
                                'id' => ''
                            ]
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'name' => 'channel',
                                'title' => 'Channel',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => Channel::class,
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'name' => 'accountId',
                                'title' => 'Account',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => Account::class,
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'name' => 'batch',
                                'title' => 'Batch',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => Batch::class,
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'name' => OrdersController::FILTER_SHIPPING_ALIAS_NAME,
                                'title' => 'Shipping Method',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => Shipping::class,
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'name' => 'tag',
                                'title' => 'Tags',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => OrdersTableTagColumns::class,
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => true,
                            'variables' => [
                                'name' => 'fulfilmentChannel',
                                'title' => 'Fulfilment Channel',
                                'searchField' => false,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => OrdersTableFulfilmentChannelColumns::class,
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'isBoolean' => true,
                                'name' => 'archived',
                                'title' => 'Is Archived',
                                'isOptional' => true,
                                'emptyValue' => true,
                                'options' => [
                                    [
                                        'value' => true,
                                        'title' => 'Yes'
                                    ],
                                    [
                                        'value' => false,
                                        'title' => 'No',
                                        'selected' => true
                                    ],
                                ]
                            ],
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'isBoolean' => true,
                                'name' => 'buyerMessage',
                                'title' => 'Has Buyer Message',
                                'isOptional' => true,
                                'options' => [
                                    [
                                        'value' => true,
                                        'title' => 'Yes'
                                    ],
                                    [
                                        'value' => false,
                                        'title' => 'No'
                                    ],
                                ]
                            ],
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'isBoolean' => true,
                                'name' => 'giftMessage',
                                'title' => 'Has Gift Message',
                                'isOptional' => true,
                                'options' => [
                                    [
                                        'value' => true,
                                        'title' => 'Yes'
                                    ],
                                    [
                                        'value' => false,
                                        'title' => 'No'
                                    ],
                                ]
                            ],
                        ],
                    ]
                ],
            ],
        ],
        'stateFilters' => [
            [
                'name' => 'All Orders',
                'id' => 'allOrdersCount',
                'subid' => 'allOrdersCountSub',
                'statusColourClass' => '',
                'filter' => json_encode(
                    [
                        'status' => [
                        ]
                    ]
                )
            ],
            [
                'name' => 'Awaiting Payment',
                'id' => 'awaitingPaymentCount',
                'subid' => 'awaitingPaymentCountSub',
                'statusColourClass' => 'awaiting-payment',
                'filter' => json_encode(
                    [
                        'status' => [
                            Status::AWAITING_PAYMENT
                        ]
                    ]
                )
            ],
            [
                'name' => 'New Orders',
                'id' => 'newOrdersCount',
                'subid' => 'newOrdersCountSub',
                'statusColourClass' => 'new',
                'filter' => json_encode(
                    [
                        'status' => [
                            Status::NEW_ORDER
                        ]
                    ]
                )
            ],
            [
                'name' => 'Processing',
                'id' => 'processingCount',
                'subid' => 'processingCountSub',
                'statusColourClass' => 'processing',
                'filter' => json_encode(
                    [
                        'status' => [
                            Status::DISPATCHING,
                            Status::CANCELLING,
                            Status::REFUNDING
                        ]
                    ]
                )
            ],
            [
                'name' => 'Dispatched',
                'id' => 'dispatchedCount',
                'subid' => 'dispatchedCountSub',
                'statusColourClass' => 'dispatched',
                'filter' => json_encode(
                    [
                        'status' => [
                            Status::DISPATCHED
                        ]
                    ]
                )
            ],
            [
                'name' => 'Cancelled',
                'id' => 'cancelledAndRefundedCount',
                'subid' => 'cancelledAndRefundedCountSub',
                'statusColourClass' => 'cancelled',
                'filter' => json_encode(
                    [
                        'status' => [
                            Status::CANCELLED,
                            Status::REFUNDED
                        ]
                    ]
                )
            ],
        ],
    ],
];
