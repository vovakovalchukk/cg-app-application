<?php

use CG\Product\Csv\Stock\Mapper\Observer as StockMapperObserver;
use CG\Product\Csv\Stock\Mapper\Subscriber\StaticFields;
use CG\Product\Csv\Stock\Mapper\Subscriber\StockFields;
use CG\Product\Csv\Stock\Mapper\Subscriber\VatFields;

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
                        ['subscriber' => VatFields::class],
                        ['subscriber' => StockFields::class],
                    ],
                ],
            ],
        ],
        'preferences' => [],
    ],
];