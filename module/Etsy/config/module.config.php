<?php
use CG\Channel\Service as ChannelService;
use CG\Etsy\Account\CreationService as AccountCreator;
use Etsy\Controller\AccountController;
use Zend\Mvc\Router\Http\Segment;
use function CG\Stdlib\hyphenToClassname;

return [
    'CG' => [
        'global' => [
            'white_listed_routes' => [AccountController::ROUTE_REGISTER => true],
        ],
    ],
    'SetupWizard' => [
        'SetupWizard' => [
            'white_listed_routes' => [AccountController::ROUTE_REGISTER => true],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view/',
        ],
        'template_map' => [
            ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . hyphenToClassname(AccountCreator::CHANNEL) => dirname(__DIR__) . '/view/etsy/account/settings.phtml',
        ],
    ],
    'router' => [
        'routes' => [
            AccountController::ROUTE_INITIALISE => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/etsy/initialise[/:account]',
                    'defaults' => [
                        'controller' => AccountController::class,
                        'action' => 'initialise',
                    ]
                ],
            ],
            AccountController::ROUTE_REGISTER => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/etsy/register[/:account]',
                    'defaults' => [
                        'controller' => AccountController::class,
                        'action' => 'register',
                    ]
                ],
            ],
        ],
    ],
];
