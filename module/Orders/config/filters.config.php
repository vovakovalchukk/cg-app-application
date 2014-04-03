<?php
use Orders\Order\FilterService;

$dateFormat = 'd/m/y';

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
                            'type' => 'DateRange',
                            'variables' => [
                                'fieldName' => 'purchaseDate',
                                'time' => [
                                    'hours' => date('H'),
                                    'minutes' => date('i')
                                ],
                                'options' => [
                                    [
                                        'title' => 'All Time',
                                        'from' => 'All',
                                        'to' => 'All'
                                    ],
                                    [
                                        'title' => 'Today',
                                        'from' => date($dateFormat),
                                        'to' => date($dateFormat)
                                    ],
                                    [
                                        'title' => 'Last 7 days',
                                        'from' => date($dateFormat, strtotime("-7 days")),
                                        'to' => date($dateFormat)
                                    ],
                                    [
                                        'title' => 'Month to date',
                                        'from' => date($dateFormat, strtotime('first day of ' . date('F Y'))),
                                        'to' => date($dateFormat)
                                    ],
                                    [
                                        'title' => 'Year to date',
                                        'from' => date($dateFormat, strtotime('first day of January ' . date('Y'))),
                                        'to' => date($dateFormat)
                                    ],
                                    [
                                        'title' => 'The previous month',
                                        'from' => date($dateFormat, strtotime('first day of last month ')),
                                        'to' => date($dateFormat, strtotime('last day of last month ')),
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'CustomSelectGroup',
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
                            'type' => 'Text',
                            'variables' => [
                                'fieldName' => 'search',
                                'placeholder' => 'Search for...',
                                'class' => '',
                                'value' => ''
                            ],
                        ],
                        [
                            'type' => 'Buttons',
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
                            'type' => 'CustomSelectGroup',
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
                            'type' => 'NumberRange',
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