<?php
use CG\Account\Client\Storage\Api as AccountStorage;
use CG\Account\Client\Service as AccountService;
use CG\Amazon\Signer as AmazonSigner;
USE CG\Amazon\Account as AmazonAccount;
use CG\Ebay\Client\TradingApi;
use Guzzle\Http\Client as GuzzleHttpClient;
use Settings\Module;
use Settings\Controller\IndexController;
use Settings\Controller\ChannelController;
use Settings\Controller\EbayController;
use Settings\Controller\AmazonController;
use Settings\Controller\InvoiceController;
use CG_UI\View\DataTable;
use Settings\Channel\Service;
use Zend\View\Model\ViewModel;
use CG\Account\Client\StorageInterface as AccountStorageInterface;
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\OrganisationUnit\StorageInterface as OUStorageInterface;
use CG\OrganisationUnit\Storage\Api as OUApiStorage;
use CG\Stdlib\Log\LoggerInterface;
use CG\Log\Logger;
use CG\Template\Storage\Object as TemplateObjectStorage;
use CG\Template\Storage\Api as TemplateApiStorage;
use CG\Template\Service as TemplateService;
use CG\Template\Repository as TemplateRepository;
use Settings\Factory\SidebarNavFactory;

return [
    'navigation' => [
        'sidebar-navigation' => [
            'Channel Management' => [
                'label' => 'Channel Management',
                'route' => Module::ROUTE.'/'.ChannelController::ROUTE,
                'class' => 'heading-medium',
                'pages' => [
                    ChannelController::ROUTE_CHANNELS => [
                        'label' => ChannelController::ROUTE_CHANNELS,
                        'title' => ChannelController::ROUTE_CHANNELS,
                        'route' => Module::ROUTE.'/'.ChannelController::ROUTE.'/'.ChannelController::ROUTE_CHANNELS
                    ]
                ]
            ],
            'Invoices' => [
                'label' => 'Invoices',
                'route' => Module::ROUTE.'/'.InvoiceController::ROUTE.'/'.InvoiceController::ROUTE_DESIGNER,
                'class' => 'heading-medium',
                'pages' => [
                    [
                        'label' => InvoiceController::ROUTE_DESIGNER,
                        'title' => InvoiceController::ROUTE_DESIGNER,
                        'route' => Module::ROUTE.'/'.InvoiceController::ROUTE.'/'.InvoiceController::ROUTE_DESIGNER
                    ],
                ]
            ]
        ],
        'application-navigation' => [
            'settings' => [
                'label'  => 'Settings',
                'route'  => Module::ROUTE,
                'sprite' => 'sprite-settings-18-white',
                'order'  => 20
            ]
        ]
    ],
    'service_manager' => [
        'factories' => [
            'sidebar-navigation'  => SidebarNavFactory::class,
        ]
    ],
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
                    ChannelController::ROUTE => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/channel',
                            'defaults' => [
                                'controller' => ChannelController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            ChannelController::ROUTE_CHANNELS => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/sales',
                                    'defaults' => [
                                        'controller' => ChannelController::class,
                                        'action' => 'list',
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'Sales Channel Ebay' => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/ebay',
                                            'defaults' => [
                                                'controller' => EbayController::class,
                                                'action' => 'save'
                                            ]
                                        ],
                                        'may_terminate' => true
                                    ],
                                    AmazonAccount::ROUTE => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => [
                                            'route' => '/amazon/:region',
                                            'defaults' => [
                                                'controller' => AmazonController::class,
                                                'action' => 'save'
                                            ]
                                        ],
                                        'may_terminate' => true
                                    ],
                                    ChannelController::AJAX_ROUTE => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/ajax',
                                            'defaults' => [
                                                'action' => 'listAjax',
                                            ]
                                        ],
                                    ],
                                    ChannelController::CREATE_ROUTE => [
                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'options' => [
                                            'route' => '/create',
                                            'defaults' => [
                                                'action' => 'create'
                                            ]
                                        ],
                                        'may_terminate' => true
                                    ],
                                    ChannelController::ACCOUNT_ROUTE => [
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => [
                                            'route' => '/:account',
                                            'defaults' => [
                                                'action' => 'account'
                                            ],
                                            'constraints' => [
                                                'account' => '[0-9]*'
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            ChannelController::ACCOUNT_STATUS_ROUTE => [
                                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                                'options' => [
                                                    'route' => '/enable',
                                                    'defaults' => [
                                                        'action' => 'statusAjax',
                                                    ]
                                                ],
                                            ],
                                            ChannelController::ACCOUNT_AJAX_ROUTE => [
                                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                                'options' => [
                                                    'route' => '/ajax',
                                                    'defaults' => [
                                                        'action' => 'accountUpdate'
                                                    ],
                                                ],
                                            ],
                                            ChannelController::ACCOUNT_DELETE_ROUTE => [
                                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                                'options' => [
                                                    'route' => '/delete',
                                                    'defaults' => [
                                                        'action' => 'delete',
                                                    ]
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    InvoiceController::ROUTE => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/invoice',
                            'defaults' => [
                                'controller' => InvoiceController::class,
                                'action' => 'index',
                                'sidebar' => Module::SIDEBAR_TEMPLATE
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            InvoiceController::ROUTE_MAPPING => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/mapping',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'mapping',
                                    ]
                                ],
                            ],
                            InvoiceController::ROUTE_DESIGNER => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/designer',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'design',
                                    ]
                                ],
                            ],
                            InvoiceController::ROUTE_FETCH => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/fetch',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'fetch'
                                    ]
                                ],
                                'may_terminate' => true
                            ],
                            InvoiceController::ROUTE_SAVE => [
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true
                            ]
                        ]
                    ]
                 ]
            ]
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
            dirname(dirname(dirname(__DIR__))) . '/public' . Module::PUBLIC_FOLDER . 'template',
        ],
        'template_map' => [
            ChannelController::ACCOUNT_TEMPLATE => dirname(__DIR__) . '/view/settings/channel/account.phtml',
            ChannelController::ACCOUNT_CHANNEL_FORM_BLANK_TEMPLATE => dirname(__DIR__) . '/view/settings/channel/account/channel_form_blank.phtml',
        ]
    ],
    'di' => [
        'instance' => [
            'preferences' => [
                AccountStorageInterface::class => AccountApiStorage::class,
                OUStorageInterface::class => OUApiStorage::class,
                LoggerInterface::class => Logger::class
            ],
            'aliases' => [
                'EbayGuzzle' => GuzzleHttpClient::class,
                'AccountList' => DataTable::class,
                'AccountListSettings' => DataTable\Settings::class,
                'ChannelTokenStatusMustacheJS' => ViewModel::class,
                'ChannelStatusJS' => viewModel::class,
                'ChannelDeleteJavascript' => ViewModel::class,
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
            'EbayGuzzle' => [
                'parameters' => [
                    'baseUrl' => 'https://api.ebay.com/ws/api.dll'
                ]
            ],
            AmazonSigner::class => array(
                'parameters' => array(
                    'secretKey' => 'Tp6B7AEOI8piy6bbSN3n5fmIZgbqWDlTvaxuDBBD',
                    'httpVerb' => 'GET'
                )
            ),
            TradingApi::class => [
                'parameters' => [
                    'client' => 'EbayGuzzle',
                    'developerId' => '39b27d4e-07e2-4298-8eaa-7614e79dba4c',
                    'applicationName' => 'ChannelG-9b1e-4478-a742-146c81a2b5a9',
                    'certificateId' => 'fa030731-18cc-4087-a06e-605d63113625'
                ]
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
                        ['child' => 'ChannelTokenStatusMustacheJS', 'captureTo' => 'javascript', 'append' => true],
                        ['child' => 'ChannelStatusJS', 'captureTo' => 'javascript', 'append' => true],
                        ['child' => 'ChannelDeleteJavascript', 'captureTo' => 'javascript', 'append' => true],
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
                    'scrollHeightAuto' => true,
                    'footer' => false,
                ]
            ],
            'ChannelTokenStatusMustacheJS' => [
                'parameters' => [
                    'template' => 'settings/channel/javascript/mustache-token.js',
                ],
            ],
            'ChannelStatusJS' => [
                'parameters' => [
                    'template' => 'settings/channel/javascript/enableChannel.js',
                    'variables' => [
                        'route' => implode(
                            '/',
                            [
                                Module::ROUTE,
                                ChannelController::ROUTE,
                                ChannelController::ROUTE_CHANNELS,
                                ChannelController::ACCOUNT_ROUTE,
                                ChannelController::ACCOUNT_STATUS_ROUTE,
                            ]
                        ),
                    ],
                ],
            ],
            'ChannelDeleteJavascript' => [
                'parameters' => [
                    'template' => 'settings/channel/javascript/deleteChannel.js',
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
            AccountStorage::class => [
                'parameters' => [
                    'client' => 'account_guzzle'
                ]
            ],
            AccountService::class => [
                'parameters' => [
                    'repository' => AccountStorage::class
                ]
            ],
            TemplateApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            TemplateService::class => [
                'parameters' => [
                    'repository' => TemplateRepository::class
                ]
            ],
            TemplateRepository::class => [
                'parameters' => [
                    'storage' => TemplateObjectStorage::class,
                    'repository' => TemplateApiStorage::class
                ]
            ]
        ]
    ]
];