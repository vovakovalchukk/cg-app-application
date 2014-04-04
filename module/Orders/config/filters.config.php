<?php
use Orders\Order\FilterService;

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
                            'template' => 'elements/date-range.mustache',
                            'variables' => [
                                'fieldName' => 'purchaseDate',
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
                            'template' => 'elements/custom-select-group.mustache',
                            'variables' => [
                                'fieldName' => 'status',
                                'title' => 'Status',
                                'id' => 'filter-status',
                                'options' => [
                                    [
                                        'title' => 'New'
                                    ],
                                    [
                                        'title' => 'Processing'
                                    ],
                                    [
                                        'title' => 'Dispatched'
                                    ]
                                ],
                            ],
                        ],
                        [
                            'template' => 'elements/text.mustache',
                            'variables' => [
                                'fieldName' => 'search',
                                'placeholder' => 'Search for...',
                                'class' => '',
                                'value' => ''
                            ],
                        ],
                        [
                            'template' => 'elements/buttons.mustache',
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
                            'template' => 'elements/custom-select-group.mustache',
                            'variables' => [
                                'fieldName' => 'shippingAddressCountry',
                                'title' => 'Include Country',
                                'isOptional' => true,
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
                            'template' => 'elements/number-range.mustache',
                            'variables' => [
                                'fieldName' => 'total',
                                'title' => 'Total',
                                'isOptional' => true,
                                'id' => ''
                            ]
                        ]
                    ]
                ],
            ],
        ],
    ],
];