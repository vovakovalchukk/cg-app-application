<?php
use CG\Channel\Service as ChannelService;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [
            ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . 'Shipstation' => dirname(__DIR__) . '/view/ship-station/settings_account.phtml',
        ]
    ],
    'router' => [
        'routes' => [

        ]
    ]
];