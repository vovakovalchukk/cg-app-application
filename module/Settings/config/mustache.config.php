<?php
use Settings\Module;
use CG_UI\Module as UiModule;

return [
    'mustache' => [
        'template' => [
            'map' => [
                'accountList' => [
                    'account' => Module::PUBLIC_FOLDER . 'template/columns/account.mustache',
                    'channel' => UiModule::PUBLIC_FOLDER . 'templates/columns/channel.mustache',
                    'enable' => Module::PUBLIC_FOLDER . 'template/columns/enable.mustache',
                    'manage' => Module::PUBLIC_FOLDER . 'template/columns/manage.mustache',
                    'status' => Module::PUBLIC_FOLDER . 'template/columns/accountStatus.mustache',
                    'tokenStatus' => Module::PUBLIC_FOLDER . 'template/columns/tokenStatus.mustache',
                    'tradingCompany' => Module::PUBLIC_FOLDER . 'template/columns/tradingCompany.mustache',
                    'stockManagement' => Module::PUBLIC_FOLDER . 'template/columns/stockManagement.mustache'
                ],
            ],
        ],
    ],
];