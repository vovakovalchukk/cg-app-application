<?php

use CG_UI\View\Filters\Service as FilterService;
use Filters\Service as FilterConfigService;
use Orders\Controller\OrdersController;

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
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_DATE_RANGE),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_STATUS),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_SEARCH),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_MORE),
                        FilterConfigService::getFilter(
                            FilterConfigService::FILTER_ORDER_BUTTONS,
                            [
                                'buttons' => [
                                    FilterConfigService::FILTER_ORDER_BUTTON_APPLY,
                                    FilterConfigService::FILTER_ORDER_BUTTON_CLEAR,
                                    FilterConfigService::FILTER_ORDER_BUTTON_SAVE
                                ]
                            ]
                        )
                    ],
                ],
                [
                    'type' => 'Row',
                    'filters' => [
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_SHIPPING_COUNTRY),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_CURRENCY_CODE),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_TOTAL),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_WEIGHT),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_CHANNEL),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_ACCOUNT),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_BATCH),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_SHIPPING_METHOD),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_TAGS),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_FULFILMENT_CHANNEL),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_IS_ARCHIVED),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_BUYER_MESSAGE),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_GIFT_MESSAGE),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_IS_DISPATCHABLE),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_MARKETPLACE),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_PRINTED),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_EMAILED),
                        FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_LABEL_PRINTED),
                    ]
                ]
            ],
        ],
        'stateFilters' => FilterConfigService::getFilter(FilterConfigService::FILTER_ORDER_STATE_FILTERS)
    ],
];
