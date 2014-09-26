<?php
use Products\Module;
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
            ],
        ],
    ],
];