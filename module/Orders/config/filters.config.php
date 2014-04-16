<?php
use Orders\Order\FilterService;
use Orders\Order\TableService\OrdersTableTagColumns;
use Orders\Order\Filter\Channel;
use Orders\Order\Filter\Account;

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
                            'type' => 'date-range',
                            'variables' => [
                                'filterName' => 'purchaseDate',
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
                                        'to' => strtotime('midnight first day of this month'),
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'customSelectGroup',
                            'variables' => [
                                'filterName' => 'status',
                                'title' => 'Status',
                                'id' => 'filter-status',
                                'searchField' => true,
                                'concatenate' => true,
                                'options' => [
                                    [
                                        'title' => 'New',
                                        'value' => 'new'
                                    ],
                                    [
                                        'title' => 'Processing',
                                        'value' => 'processing'
                                    ],
                                    [
                                        'title' => 'Dispatched',
                                        'value' => 'dispatched'
                                    ]
                                ],
                            ],
                        ],
                        [
                            'type' => 'text',
                            'variables' => [
                                'filterName' => 'search',
                                'placeholder' => 'Search for...',
                                'class' => '',
                                'value' => ''
                            ],
                        ],
                        [
                            'type' => 'more',
                            'variables' => [
                                'title' => 'More',
                                'class' => 'more',
                                'filterName' => 'more'
                            ],
                        ],
                        [
                            'type' => 'buttons',
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
                            'type' => 'customSelectGroup',
                            'visible' => true,
                            'variables' => [
                                'filterName' => 'shippingAddressCountry',
                                'title' => 'Include Country',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => [
                                    [
                                        'title' => 'UK'
                                    ],
                                    [
                                        'title' => 'Austria'
                                    ],
                                    [
                                        'title' => 'Croatia'
                                    ],
                                    [
                                        'title' => 'Cyprus'
                                    ],
                                    [
                                        'title' => 'France'
                                    ],
                                    [
                                        'title' => 'Germany'
                                    ],
                                    [
                                        'title' => 'Italy'
                                    ],
                                    [
                                        'title' => 'Spain'
                                    ]
                                ]
                            ],
                        ],
                        [
                            'type' => 'numberRange',
                            'visible' => true,
                            'variables' => [
                                'filterName' => 'total',
                                'title' => 'Total',
                                'isOptional' => true,
                                'id' => ''
                            ]
                        ],
                        [
                            'type' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'filterName' => 'channel',
                                'title' => 'Channel',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => Channel::class,
                        ],
                        [
                            'type' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'filterName' => 'account',
                                'title' => 'Account',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => Account::class,
                        ],
                        [
                            'type' => 'customSelectGroup',
                            'visible' => false,
                            'variables' => [
                                'filterName' => 'tags',
                                'title' => 'Tags',
                                'searchField' => true,
                                'isOptional' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => OrdersTableTagColumns::class,
                        ],
                        [
                            'type' => 'customSelect',
                            'visible' => false,
                            'variables' => [
                                'filterName' => 'archived',
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
                            'type' => 'customSelect',
                            'visible' => false,
                            'variables' => [
                                'filterName' => 'buyerMessage',
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
                'name' => 'New Orders',
                'filter' => json_encode(
                    [
                        'filters' => [
                            'status' => 'New'
                        ],
                        'optional' => []
                    ]
                )
            ],
            [
                'name' => 'Processing',
                'filter' => json_encode(
                    [
                        'filters' => [
                            'status' => 'Processing'
                        ],
                        'optional' => []
                    ]
                )
            ],
            [
                'name' => 'Dispatched',
                'filter' => json_encode(
                    [
                        'filters' => [
                            'status' => 'Dispatched'
                        ],
                        'optional' => []
                    ]
                )
            ],
        ],
    ],
];