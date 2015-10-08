<?php
use CG\Listing\Unimported\Status as UnimportedListingStatus;
use CG_UI\View\Filters\Service as FilterService;
use Filters\Options\Account;
use Filters\Options\Channel;
use Filters\Options\Marketplace;
use Products\Controller\ListingsController;

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
        ListingsController::FILTER_TYPE => [
            'rows' => [
                [
                    'type' => 'Row',
                    'filters' => [
                        [
                            'filterType' => 'date-range',
                            'variables' => [
                                'name' => 'createdDate',
                                'title' => 'Found',
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
                                'name' => 'channel',
                                'title' => 'Channel',
                                'searchField' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => Channel::class,
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'variables' => [
                                'name' => 'accountId',
                                'title' => 'Account',
                                'searchField' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => Account::class,
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'variables' => [
                                'name' => 'marketplace',
                                'title' => 'Marketplace',
                                'searchField' => true,
                                'concatenate' => true,
                                'options' => []
                            ],
                            'optionsProvider' => Marketplace::class,
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
                                        'title' => ucwords(str_replace("_", " ", UnimportedListingStatus::NOT_STARTED)),
                                        'value' => UnimportedListingStatus::NOT_STARTED
                                    ],
                                    [
                                        'title' => ucwords(UnimportedListingStatus::IMPORTING),
                                        'value' => UnimportedListingStatus::IMPORTING
                                    ],
                                    [
                                        'title' => ucwords(UnimportedListingStatus::ERROR),
                                        'value' => UnimportedListingStatus::ERROR
                                    ],
                                ],
                            ],
                        ],
                        [
                            'filterType' => 'customSelectGroup',
                            'variables' => [
                                'isBoolean' => true,
                                'name' => 'hidden',
                                'title' => 'Show Hidden',
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
                            'filterType' => 'search',
                            'variables' => [
                                'name' => 'searchTerm',
                                'placeholder' => 'Search for...',
                                'class' => '',
                                'value' => ''
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
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        ]
    ]
];
