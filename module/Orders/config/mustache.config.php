<?php
use Orders\Module;

return [
    'mustache' => [
        'template' => [
            'map' => [
                'orderList' => [
                    'accountId' => Module::PUBLIC_FOLDER . 'template/columns/accountId.mustache',
                    'batch' => Module::PUBLIC_FOLDER . 'template/columns/batch.mustache',
                    'billingAddressFullName' => Module::PUBLIC_FOLDER . 'template/columns/billingAddressFullName.mustache',
                    'buyerMessage' => Module::PUBLIC_FOLDER . 'template/columns/buyerMessage.mustache',
                    'channel' => Module::PUBLIC_FOLDER . 'template/columns/channel.mustache',
                    'custom-tag' => Module::PUBLIC_FOLDER . 'template/columns/custom-tag.mustache',
                    'dispatchDate' => Module::PUBLIC_FOLDER . 'template/columns/dispatchDate.mustache',
                    'externalId' => Module::PUBLIC_FOLDER . 'template/columns/externalId.mustache',
                    'id' => Module::PUBLIC_FOLDER . 'template/columns/id.mustache',
                    'printedDate' => Module::PUBLIC_FOLDER . 'template/columns/printedDate.mustache',
                    'purchaseDate' => Module::PUBLIC_FOLDER . 'template/columns/purchaseDate.mustache',
                    'shippingMethod' => Module::PUBLIC_FOLDER . 'template/columns/shippingMethod.mustache',
                    'status' => Module::PUBLIC_FOLDER . 'template/columns/status.mustache',
                    'tag' => Module::PUBLIC_FOLDER . 'template/columns/tag.mustache',
                    'total' => Module::PUBLIC_FOLDER . 'template/columns/total.mustache'
                ],
            ],
        ],
    ],
];