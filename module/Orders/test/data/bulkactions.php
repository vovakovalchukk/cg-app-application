<?php
return [
    'bulkActions' => [
        [
            'title' => 'Invoice',
            'class' => 'invoice',
            'sub-actions' => [
                ['title' => 'by SKU', 'action' => 'invoices-sku'],
                ['title' => 'by Title', 'action' => 'invoices-title']
            ]
        ],
        [
            'title' => 'Dispatch',
            'class' => 'dispatch'
        ],
        [
            'title' => 'Tag / Untag',
            'class' => 'tag-untag',
            'sub-actions' => [

            ]
        ],
        [
            'title' => 'Download CSV',
            'class' => 'download-csv'
        ],
        [
            'title' => 'Courier',
            'class' => 'courier',
            'sub-actions' => [
                ['title' => 'Create Royal Mail CSV', 'action' => 'royal-mail-csv']
            ]
        ],
        [
            'title' => 'Batch',
            'class' => 'batch',
            'sub-actions' => [
                ['title' => 'Remove', 'action' => 'remove-from-batch']
            ]
        ],
        [
            'title' => 'Archive',
            'class' => 'archive'
        ]
    ]
];