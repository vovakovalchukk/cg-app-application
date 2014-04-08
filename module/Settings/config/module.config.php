<?php
use Settings\Module;
use Settings\Controller\IndexController;
use Settings\Controller\ChannelController;
use CG_UI\View\DataTable;
use Settings\Channel\Service;
use Zend\View\Model\ViewModel;
use CG\Account\Client\StorageInterface as AccountStorageInterface;
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\OrganisationUnit\StorageInterface as OUStorageInterface;
use CG\OrganisationUnit\Storage\Api as OUApiStorage;

return [
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/settings',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'subHeader' => Module::SUBHEADER_TEMPLATE,
                        'sidebar' => Module::SIDEBAR_TEMPLATE,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    ChannelController::LIST_ROUTE => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/channel',
                            'defaults' => [
                                'controller' => ChannelController::class,
                                'action' => 'list',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            ChannelController::LIST_AJAX_ROUTE => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/ajax',
                                    'defaults' => [
                                        'action' => 'listAjax',
                                    ]
                                ],
                            ],
                            ChannelController::CHANNEL_ROUTE => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/:channel',
                                    'constraints' => [
                                        'channel' => '[0-9]+'
                                    ],
                                    'defaults' => []
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    ChannelController::CHANNEL_DELETE_ROUTE => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/delete',
                                            'defaults' => []
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'di' => [
        'instance' => [
            'preferences' => [
                AccountStorageInterface::class => AccountApiStorage::class,
                OUStorageInterface::class => OUApiStorage::class,
            ],
            'aliases' => [
                'AccountList' => DataTable::class,
                'AccountListSettings' => DataTable\Settings::class,
                'MustacheStatus' => ViewModel::class,
                'MustacheTokenStatus' => ViewModel::class,
                'EnableChannelJavascript' => viewModel::class,
                'AccountEnableColumn' => DataTable\Column::class,
                'AccountStatusColumn' => DataTable\Column::class,
                'AccountChannelColumn' => DataTable\Column::class,
                'AccountAccountColumn' => DataTable\Column::class,
                'AccountTradingCompanyColumn' => DataTable\Column::class,
                'AccountTokenStatusColumn' => DataTable\Column::class,
                'AccountManageColumn' => DataTable\Column::class,
                'AccountEnableColumnView' => ViewModel::class,
                'AccountStatusColumnView' => ViewModel::class,
                'AccountChannelColumnView' => ViewModel::class,
                'AccountAccountColumnView' => ViewModel::class,
                'AccountTradingCompanyColumnView' => ViewModel::class,
                'AccountTokenStatusColumnView' => ViewModel::class,
                'AccountManageColumnView' => ViewModel::class,
            ],
            AccountApiStorage::class => [
                'parameters' => [
                    'client' => 'account_guzzle',
                ],
            ],
            Service::class => [
                'parameters' => [
                    'accountList' => 'AccountList',
                ],
            ],
            'AccountList' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'accounts',
                    ],
                ],
                'injections' => [
                    'addChild' => [
                        ['child' => 'MustacheStatus', 'captureTo' => 'javascript', 'append' => true],
                        ['child' => 'MustacheTokenStatus', 'captureTo' => 'javascript', 'append' => true],
                        ['child' => 'EnableChannelJavascript', 'captureTo' => 'javascript', 'append' => true],
                    ],
                    'addColumn' => [
                        ['column' => 'AccountEnableColumn'],
                        ['column' => 'AccountStatusColumn'],
                        ['column' => 'AccountChannelColumn'],
                        ['column' => 'AccountAccountColumn'],
                        ['column' => 'AccountTradingCompanyColumn'],
                        ['column' => 'AccountTokenStatusColumn'],
                        ['column' => 'AccountManageColumn'],
                    ],
                    'setVariable' => [
                        ['name' => 'settings', 'value' => 'AccountListSettings']
                    ],
                ],
            ],
            'AccountListSettings' => [
                'parameters' => [

                ]
            ],
            'MustacheStatus' => [
                'parameters' => [
                    'template' => 'settings/channel/javascript/mustache-status.js',
                ],
            ],
            'MustacheTokenStatus' => [
                'parameters' => [
                    'template' => 'settings/channel/javascript/mustache-token.js',
                ],
            ],
            'EnableChannelJavascript' => [
                'parameters' => [
                    'template' => 'settings/channel/javascript/enableChannel.js',
                ],
            ],
            'AccountEnableColumn' => [
                'parameters' => [
                    'templateId' => 'enable',
                    'viewModel' => 'AccountEnableColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '100px',
                ],
            ],
            'AccountStatusColumn' => [
                'parameters' => [
                    'templateId' => 'status',
                    'viewModel' => 'AccountStatusColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '100px',
                ],
            ],
            'AccountChannelColumn' => [
                'parameters' => [
                    'templateId' => 'channel',
                    'viewModel' => 'AccountChannelColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountAccountColumn' => [
                'parameters' => [
                    'templateId' => 'account',
                    'viewModel' => 'AccountAccountColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountTradingCompanyColumn' => [
                'parameters' => [
                    'templateId' => 'tradingCompany',
                    'viewModel' => 'AccountTradingCompanyColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountTokenStatusColumn' => [
                'parameters' => [
                    'templateId' => 'tokenStatus',
                    'viewModel' => 'AccountTokenStatusColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountManageColumn' => [
                'parameters' => [
                    'templateId' => 'manage',
                    'viewModel' => 'AccountManageColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountEnableColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Enable'],
                    'template' => 'value.phtml',
                ],
            ],
            'AccountStatusColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Status'],
                    'template' => 'value.phtml',
                ],
            ],
            'AccountChannelColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Sales Channel'],
                    'template' => 'value.phtml',
                ],
            ],
            'AccountAccountColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Account Name'],
                    'template' => 'value.phtml',
                ],
            ],
            'AccountTradingCompanyColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Trading Company'],
                    'template' => 'value.phtml',
                ],
            ],
            'AccountTokenStatusColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Token Expires'],
                    'template' => 'value.phtml',
                ],
            ],
            'AccountManageColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Manage'],
                    'template' => 'value.phtml',
                ],
            ],
        ],
    ],
];