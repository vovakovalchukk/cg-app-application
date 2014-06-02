<?php
use Orders\Order\CountryService;
use Orders\Order\CurrencyService;
use Orders\Order\FilterService;
use Orders\Order\TableService\OrdersTableTagColumns;
use Orders\Order\Filter\Channel;
use Orders\Order\Filter\Account;
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
        'orders' => [
            'rows' => [
                [
                    'type' => 'Row',
                    'filters' => [
                        [
                            'filterType' => 'date-range',
                            'variables' => [
                                'name' => 'purchaseDate',
                                'time' => [
                                    'hours' => date('H'),
                                    'minutes' => date('i')
                                ],
                                'options' => [
                                    [
                                        'title' => 'All Time'
                                    ],
                                    [
                                        'title' => 'Today',
                                        'from' => strtotime("today"),
                                        'to' => strtotime("now")
                                    ],
                                    [
                                        'title' => 'Last 7 days',
                                        'from' => strtotime("-7 days"),
                                        'to' => strtotime("now")
                                    ],
                                    [
                                        'title' => 'Month to date',
                                        'from' => strtotime('midnight first day of this month'),
                                        'to' => strtotime("now")
                                    ],
                                    [
                                        'title' => 'Year to date',
                                        'from' => strtotime('first day of January'),
                                        'to' => strtotime("now")
                                    ],
                                    [
                                        'title' => 'The previous month',
                                        'from' => strtotime('midnight first day of last month'),
                                        'to' => strtotime('23:59 last day of last month'),
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
                                'name' => 'search',
                                'placeholder' => 'Search for...',
                                'class' => '',
                                'value' => ''
                            ],
                        ],
                        [
                            'filterType' => 'more',
                            'variables' => [
                                'title' => 'More',
                                'class' => 'more',
                                'name' => 'more'
                            ],
                        ],
                        [
                            'filterType' => 'buttons',
                            'variables' => [
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
                            'filterType' => 'customSelect',
                            'visible' => false,
                            'variables' => [
                                'name' => 'archived',
                                'title' => 'Show Archived',
                                'isOptional' => true,
                                'options' => [
                                    [
                                        'title' => 'All'
                                    ],
                                    [
                                        'title' => 'Yes'
                                    ],
                                    [
                                        'title' => 'No',
                                        'selected' => true
                                    ],
                                ]
                            ],
                        ],
                        [
                            'filterType' => 'customSelect',
                            'visible' => false,
                            'variables' => [
                                'name' => 'buyerMessage',
                                'title' => 'Buyer Message',
                                'isOptional' => true,
                                'options' => [
                                    [
                                        'title' => 'All'
                                    ],
                                    [
                                        'title' => 'Yes'
                                    ],
                                    [
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
                'name' => 'Awaiting Payment',
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
                'filter' => json_encode(
                    [
                        'status' => [
                            Status::DISPATCHED
                        ]
                    ]
                )
            ],
            [
                'name' => 'Cancelled & Refunded',
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
