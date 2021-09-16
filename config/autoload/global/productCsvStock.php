<?php

use CG\Product\Csv\Stock\Mapper\Observer as StockMapperObserver;
use CG\Product\Csv\Stock\Mapper\Subscriber\StaticFields;
use CG\Product\Csv\Stock\Mapper\Subscriber\StockFields;

return [
    'di' => [
        'definition' => [
            'class' => [
                StockMapperObserver::class => [
                    'methods' => [
                        'registerSubscriber' => [
                            'subscriber' => ['required' => true]
                        ],
                    ],
                ],
            ],
        ],
        'instance' => [
            StockMapperObserver::class => [
                'injections' => [
                    'registerSubscriber' => [
                        ['subscriber' => StaticFields::class],
                        ['subscriber' => StockFields::class],
                    ],
                ],
            ],
        ],
        'preferences' => [],
    ],
];