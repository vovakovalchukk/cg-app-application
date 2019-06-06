<?php
use CG\Channel\Service as ChannelService;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [
            ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . 'Classic' => dirname(__DIR__) . '/view/classic/account/settings.phtml',
        ],
    ],
];
