<?php
use CG\Order\Client\Pdf\OrderTable\PopulateForOrdersInterface;
use CG\Order\Client\Pdf\OrderTable\Storage\RuntimeBulk as OrderTableRelatedDataRuntimeBulkStorage;
use CG\Order\Client\Pdf\OrderTable\StorageInterface as OrderTableRelatedDataStorage;
use CG\Order\Shared\Collection as Orders;

return [
    'di' => [
        'definition' => [
            'class' => [
                PopulateForOrdersInterface::class => [
                    'methods' => [
                        'populateForOrders' => [
                            'orders' => ['type' => Orders::class, 'required' => true],
                        ],
                    ],
                ],
            ],
        ],
        'instance' => [
            'preferences' => [
                OrderTableRelatedDataStorage::class => OrderTableRelatedDataRuntimeBulkStorage::class,
            ],
        ],
    ],
];