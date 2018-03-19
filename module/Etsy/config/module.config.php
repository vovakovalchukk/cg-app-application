<?php
use CG\Channel\Service as ChannelService;
use CG\Etsy\Account\CreationService as AccountCreator;
use Etsy\Controller\AccountController;
use Zend\Mvc\Router\Http\Literal;
use function CG\Stdlib\hyphenToClassname;

return [
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
            AccountController::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/etsy/register',
                    'defaults' => [
                        'controller' => AccountController::class,
                        'action' => 'register',
                    ]
                ],
            ],
        ],
    ],
];
