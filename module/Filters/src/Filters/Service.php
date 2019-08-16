<?php
namespace Filters;

use CG\Order\Shared\Status;
use Filters\Options\Account;
use Filters\Options\Channel;
use Filters\ViewSpecModifier\Weight;
use Orders\Controller\OrdersController;
use Orders\Order\CountryService;
use Orders\Order\CurrencyService;
use Orders\Order\Filter\Batch;
use Orders\Order\Filter\Shipping;
use Orders\Order\TableService\OrdersTableFulfilmentChannelColumns;
use Orders\Order\TableService\OrdersTableTagColumns;

class Service
{
    const FILTER_ORDER_DATE_RANGE = 'orderDateRange';
    const FILTER_ORDER_STATUS = 'orderStatus';
    const FILTER_ORDER_SEARCH = 'orderSearch';
    const FILTER_ORDER_SEARCH_FIELDS = 'orderSearchFields';
    const FILTER_ORDER_MORE = 'orderMoreButton';
    const FILTER_ORDER_BUTTONS = 'orderButtons';
    const FILTER_ORDER_BUTTON_APPLY = 'orderButtonApply';
    const FILTER_ORDER_BUTTON_CLEAR = 'orderButtonClear';
    const FILTER_ORDER_BUTTON_SAVE = 'orderButtonSave';
    const FILTER_ORDER_SHIPPING_COUNTRY = 'orderShippingCountry';
    const FILTER_ORDER_CURRENCY_CODE = 'orderCurrencyCode';
    const FILTER_ORDER_TOTAL = 'orderTotal';
    const FILTER_ORDER_WEIGHT = 'orderWeight';
    const FILTER_ORDER_CHANNEL = 'orderChannel';
    const FILTER_ORDER_ACCOUNT = 'orderAccount';
    const FILTER_ORDER_BATCH = 'orderBatch';
    const FILTER_ORDER_SHIPPING_METHOD = 'orderShippingMethod';
    const FILTER_ORDER_TAGS = 'orderTags';
    const FILTER_ORDER_FULFILMENT_CHANNEL = 'orderFulfilmentChannel';
    const FILTER_ORDER_IS_ARCHIVED = 'orderIsArchived';
    const FILTER_ORDER_BUYER_MESSAGE = 'orderBuyerMessage';
    const FILTER_ORDER_GIFT_MESSAGE = 'orderGiftMessage';
    const FILTER_ORDER_STATE_FILTERS = 'orderFilterStateFilters';
    const FILTER_ORDER_IS_DISPATCHABLE = 'orderIsDispatchable';

    static protected function getOrderFilters()
    {
        $orderCountStatusGroups = Status::getOrderCountStatusGroups();

        return [
            self::FILTER_ORDER_DATE_RANGE => [
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
            self::FILTER_ORDER_STATUS => [
                'filterType' => 'customSelectGroup',
                'variables' => [
                    'name' => 'status',
                    'title' => 'Status',
                    'id' => 'filter-status',
                    'searchField' => true,
                    'concatenate' => true,
                    'options' => Status::getAllStatusesAsSelectOptions(),
                ],
            ],
            self::FILTER_ORDER_SEARCH => [
                'filterType' => 'search',
                'variables' => [
                    'name' => 'searchTerm',
                    'placeholder' => 'Search for...',
                    'class' => '',
                    'value' => ''
                ],
            ],
            self::FILTER_ORDER_SEARCH_FIELDS => [
                'filterType' => 'customSelectGroup',
                'variables' => [
                    'name' => 'searchField',
                    'title' => 'Search Fields',
                    'searchField' => true,
                    'emptyTitle' => 'Select fields',
                    'options' => [
                        ['value' => 'order.externalId',         'title' => 'Order ID', 'selected' => true],
                        ['value' => 'order.id',                 'title' => 'CG Order ID'],
                        ['value' => 'item.itemSku',             'title' => 'SKU'],
                        ['value' => 'item.itemName',            'title' => 'Product Name'],
                        ['value' => 'billing.addressFullName',  'title' => 'Buyer Name'],
                        ['value' => 'billing.emailAddress',     'title' => 'Buyer Email'],
                        ['value' => 'order.externalUsername',   'title' => 'Username'],
                        ['value' => 'shipping.addressFullName', 'title' => 'Recipient Name'],
                        ['value' => 'shipping.addressPostcode', 'title' => 'Postcode'],
                        ['value' => 'tracking.number',          'title' => 'Tracking Number'],
                    ]
                ],
            ],
            self::FILTER_ORDER_MORE => [
                'filterType' => 'more',
                'variables' => [
                    'id' => 'filter-more-button',
                    'searchField' => true,
                    'title' => 'More',
                    'class' => 'more',
                    'name' => 'more'
                ],
            ],
            self::FILTER_ORDER_BUTTONS => [
                'filterType' => 'buttons',
                'variables' => [
                    'name' => 'buttons',
                    'buttons' => []
                ]
            ],
            self::FILTER_ORDER_BUTTON_APPLY => [
                'name' => 'apply-filters',
                'value' => 'Apply Filters',
                'action' => 'apply-filters',
            ],
            self::FILTER_ORDER_BUTTON_CLEAR => [
                'name' => 'clear-filters',
                'value' => 'Clear',
                'action' => 'clear-filters',
            ],
            self::FILTER_ORDER_BUTTON_SAVE => [
                'name' => 'save-filters',
                'value' => 'Save',
                'action' => 'save-filters',
            ],
            self::FILTER_ORDER_SHIPPING_COUNTRY => [
                'filterType' => 'customSelectGroup',
                'visible' => false,
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
            self::FILTER_ORDER_CURRENCY_CODE => [
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
            self::FILTER_ORDER_TOTAL => [
                'filterType' => 'numberRange',
                'visible' => false,
                'variables' => [
                    'name' => 'total',
                    'title' => 'Total',
                    'isOptional' => true,
                    'id' => ''
                ]
            ],
            self::FILTER_ORDER_WEIGHT => [
                'filterType' => 'numberRange',
                'visible' => false,
                'variables' => [
                    'name' => 'weight',
                    'title' => 'Weight (%s)',
                    'isOptional' => true,
                    'id' => ''
                ],
                'specModifier' => Weight::class,
            ],
            self::FILTER_ORDER_CHANNEL => [
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
            self::FILTER_ORDER_ACCOUNT => [
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
            self::FILTER_ORDER_BATCH => [
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
            self::FILTER_ORDER_SHIPPING_METHOD => [
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
            self::FILTER_ORDER_TAGS => [
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
            self::FILTER_ORDER_FULFILMENT_CHANNEL => [
                'filterType' => 'customSelectGroup',
                'visible' => false,
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
            self::FILTER_ORDER_IS_ARCHIVED => [
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
            self::FILTER_ORDER_BUYER_MESSAGE => [
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
            self::FILTER_ORDER_GIFT_MESSAGE => [
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
            self::FILTER_ORDER_IS_DISPATCHABLE => [
                'filterType' => 'customSelectGroup',
                'visible' => false,
                'variables' => [
                    'isBoolean' => true,
                    'name' => 'dispatchable',
                    'title' => 'Dispatchable By Merchant',
                    'isOptional' => true,
                    'emptyValue' => true,
                    'options' => [
                        [
                            'value' => true,
                            'title' => 'Yes',
                            'selected' => true
                        ],
                        [
                            'value' => false,
                            'title' => 'No'
                        ],
                    ]
                ],
            ],
            self::FILTER_ORDER_STATE_FILTERS => [
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
                            'status' => $orderCountStatusGroups['awaitingPayment']
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
                            'status' => $orderCountStatusGroups['newOrders']
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
                            'status' => $orderCountStatusGroups['processing']
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
                            'status' => $orderCountStatusGroups['dispatched']
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
                            'status' => $orderCountStatusGroups['cancelledAndRefunded']
                        ]
                    )
                ],
                [
                    'name' => 'Errors',
                    'id' => 'errorsCount',
                    'subid' => 'errorsCountSub',
                    'statusColourClass' => 'error',
                    'hideIfZero' => true,
                    'filter' => json_encode(
                        [
                            'status' => $orderCountStatusGroups['errors']
                        ]
                    )
                ],
            ]
        ];
    }

    static function getFilter(string $filterKey, array $extra = [])
    {
        $filter = self::getOrderFilters()[$filterKey];
        if (empty($extra)) {
            return $filter;
        }

        foreach ($extra as $key => $values) {
            if (isset($filter['variables'][$key])) {
                foreach ($values as $value) {
                    $filter['variables'][$key][] = self::getOrderFilters()[$value];
                }
            }
        }

        return $filter;
    }
}
