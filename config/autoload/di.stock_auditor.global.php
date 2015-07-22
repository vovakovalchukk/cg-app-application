<?php

use CG\Stock\Auditor;
use CG\Stock\Audit\Queue;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                "StockAuditQueue" => Queue::class,
                "StockAdjustmentAuditQueue" => Queue::class
            ],
            Auditor::class => [
                'shared' => true,
                'parameters' => [
                    'stockAuditQueue' => "StockAuditQueue",
                    'stockAdjustmentAuditQueue' => "StockAdjustmentAuditQueue"
                ]
            ],
            "StockAuditQueue" => [
                'parameters' => [
                    'client' => 'reliable_redis',
                    'queueName' => 'StockAudit'
                ]
            ],
            "StockAdjustmentAuditQueue" => [
                'parameters' => [
                    'client' => 'reliable_redis',
                    'queueName' => 'StockAdjustmentAudit'
                ]
            ],
        ]
    ]
];
