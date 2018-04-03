<?php
use CG\Channel\Service as ChannelService;
use CourierExport\Controller\AccountController;
use Zend\Mvc\Router\Http\Segment;
use function CG\Stdlib\hyphenToFullyQualifiedClassname;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [
            ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . hyphenToFullyQualifiedClassname('royal-mail-click-drop', 'CourierExport') => dirname(__DIR__) . '/view/courier-export/royal-mail-click-drop/settings.phtml',
        ],
    ],
    'router' => [
        'routes' => [
            AccountController::ROUTE_SETUP => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/courierExport/:channel/setup[/:account]',
                    'defaults' => [
                        'controller' => AccountController::class,
                        'action' => 'setup',
                    ]
                ],
            ],
        ],
    ],
];