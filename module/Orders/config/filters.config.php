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
                            'template' => 'elements/date-range.mustache',
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
                            'template' => 'elements/custom-select-group.mustache',
                            'variables' => [
                                'fieldName' => 'status',
                                'title' => 'Status',
                                'id' => 'filter-status',
                                'searchField' => true,
                                'concatenate' => true,
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
                            'template' => 'elements/more.mustache',
                            'variables' => [
                                'title' => 'More',
                                'concatenate' => true,
                                'class' => 'more',
                                'id' => 'more',
                                'options' => [
                                    [
                                        'title' => 'Filter 1'
                                    ],
                                    [
                                        'title' => 'Filter 2'
                                    ],
                                    [
                                        'title' => 'Filter 3'
                                    ],
                                    [
                                        'title' => 'Filter 4'
                                    ],
                                    [
                                        'title' => 'Filter 5'
                                    ],
                                    [
                                        'title' => 'Filter 6'
                                    ],
                                    [
                                        'title' => 'Filter 7'
                                    ],
                                    [
                                        'title' => 'Filter 8'
                                    ]
                                ],
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
                            'template' => 'elements/number-range.mustache',
                            'variables' => [
                                'fieldName' => 'total',
                                'title' => 'Total',
                                'isOptional' => true,
                                'id' => ''
                            ]
                        ],
                        [
                            'template' => 'elements/number-range.mustache',
                            'variables' => [
                                'fieldName' => 'total',
                                'title' => 'Total',
                                'isOptional' => true,
                                'id' => ''
                            ]
                        ],
                        [
                            'template' => 'elements/number-range.mustache',
                            'variables' => [
                                'fieldName' => 'total',
                                'title' => 'Total',
                                'isOptional' => true,
                                'id' => ''
                            ]
                        ],
                        [
                            'template' => 'elements/number-range.mustache',
                            'variables' => [
                                'fieldName' => 'total',
                                'title' => 'Total',
                                'isOptional' => true,
                                'id' => ''
                            ]
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