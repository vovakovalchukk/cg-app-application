<?php
use Orders\Module as OrdersModule;
use Products\Module;
use Products\Controller\StockLogController;
use CG_UI\Module as UiModule;

return [
    'mustache' => [
        'template' => [
            'map' => [
                'listingList' => [
                    'id' => UiModule::PUBLIC_FOLDER . 'templates/columns/id.mustache',
                    'accountId' => UiModule::PUBLIC_FOLDER . 'templates/columns/accountId.mustache',
                    'channel' => UiModule::PUBLIC_FOLDER . 'templates/columns/channel.mustache',
                    'sku' => Module::PUBLIC_FOLDER . 'template/columns/sku.mustache',
                    'image' => UiModule::PUBLIC_FOLDER . 'templates/columns/image.mustache',
                    'title' => Module::PUBLIC_FOLDER . 'template/columns/title.mustache',
                    'createdDate' => Module::PUBLIC_FOLDER . 'template/columns/found.mustache',
                    'status' => Module::PUBLIC_FOLDER . 'template/columns/status.mustache'
                ],
                 StockLogController::MUSTACHE_PRODUCT_LOGS => [
                    'id' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/id.mustache',
                    'itid' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/itid.mustache',
                    'stid' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/stid.mustache',
                    'accountId' => UiModule::PUBLIC_FOLDER . 'templates/columns/accountId.mustache',
                    'orderId' => UiModule::PUBLIC_FOLDER . 'templates/columns/orderId.mustache',
                    'sku' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/sku.mustache',
                    'listingId' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/listingId.mustache',
                    'status' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/status.mustache',
                    'stockManagement' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/stockManagement.mustache',
                    'onHandQty' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/onHandQty.mustache',
                    'allocatedQty' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/allocatedQty.mustache',
                    'availableQty' => Module::PUBLIC_FOLDER . 'template/columns/stock-log/availableQty.mustache',
                ],
            ],
        ],
    ],
];