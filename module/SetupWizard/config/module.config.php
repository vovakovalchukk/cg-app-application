<?php
namespace SetupWizard;

use SetupWizard\Controller\IndexController;
use SetupWizard\Module;
use Zend\Mvc\Router\Http\Literal;

return [
    'navigation' => [
        'setup-navigation' => [
            'Steps' => [
                'label' => '',
                'route' => Module::ROUTE,
                'class' => '',
                'pages' => [
                    // Added by config in the ./steps directory
                ]
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'setup-navigation'  => Navigation\SidebarNavFactory::class,
        ]
    ],
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/setup',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'header'  => false,
                        'subHeader' => false,
                        'sidebar' => 'setup-wizard/layout/sidebar'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // Added by config in the ./steps directory
                ],
            ]
        ]
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy'
        ],
        'template_path_stack' => [
            __NAMESPACE__ => dirname(__DIR__) . '/view',
            PROJECT_ROOT . '/public' . Module::PUBLIC_FOLDER . 'template',
        ]
    ],
    'di' => [
        'instance' => [
            'aliases' => [
            ]
        ]
    ],
];
