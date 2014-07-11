<?php
use Settings\Module;

return [
    'mustache' => [
        'template' => [
            'map' => [
                'accountList' => [
                    'account' => Module::PUBLIC_FOLDER . 'template/columns/account.html',
                    'channel' => Module::PUBLIC_FOLDER . 'template/columns/channel.html',
                    'enable' => Module::PUBLIC_FOLDER . 'template/columns/enable.mustache',
                    'manage' => Module::PUBLIC_FOLDER . 'template/columns/manage.html',
                    'status' => Module::PUBLIC_FOLDER . 'template/columns/status.mustache',
                    'tokenStatus' => Module::PUBLIC_FOLDER . 'template/columns/tokenStatus.html',
                    'tradingCompany' => Module::PUBLIC_FOLDER . 'template/columns/tradingCompany.html',
                ],
            ],
        ]
    ],
];