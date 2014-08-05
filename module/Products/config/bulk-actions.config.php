<?php
use Products\Product\BulkActions\Service;
use CG_UI\View\BulkActions;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'ProductListBulkActions' => BulkActions::class,
                'ProductDetailBulkActions' => BulkActions::class
            ],
            Service::class => [
                'parameters' => [
                    'listPageBulkActions' => 'ProductListBulkActions',
                    'detailPageBulkActions' => 'ProductDetailBulkActions'
                ],
            ],
            'ProductListBulkActions' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'bulk-actions',
                        'class' => ['fixed-scroll'],
                    ],
                ]
            ],
            'ProductDetailBulkActions' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'bulk-actions',
                        'class' => ['bulk-actions-inline'],
                    ],
                ]
            ],
        ],
    ],
];