<?php

use CG\Stock\Mapper as StockMapper;
use CG\Stock\Locking\Entity as LockingStock;

return [
    'di' => [
        'instance' => [
            StockMapper::class => [
                'parameters' => [
                    'entityClass' => function() { return LockingStock::class; },
                ],
            ],
        ]
    ]
];