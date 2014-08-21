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
use Settings\Controller\ShippingController;
use CG_UI\View\DataTable;
use Settings\Channel\Service as ChannelService;
use Settings\Invoice\Service as InvoiceService;
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
use CG\Order\Client\Shipping\Method\Storage\Api as ShippingMethodStorage;
use CG\Order\Service\Shipping\Method\Service as ShippingMethodService;
use CG\Settings\Alias\Storage\Api as ShippingAliasStorage;
use CG\Settings\Alias\Service as ShippingAliasService;
use Zend\Mvc\Router\Http\Segment;
use Zend\Mvc\Router\Http\Literal;
use CG\Channel\Type;
use CG\Ebay\Account as EbayAccount;

return [
    'CG' => [
        'Settings' => [
            'show_to_pdf_button' => false
        ]
    ],
    'navigation' => [
        'sidebar-navigation' => [
            'Channel Management' => [
                'label' => 'Channel Management',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    Type::SALES . ' ' . ChannelController::ROUTE_CHANNELS => [
                        'label' => ucwords(Type::SALES) . ' ' . ChannelController::ROUTE_CHANNELS,
                        'title' => ucwords(Type::SALES) . ' ' . ChannelController::ROUTE_CHANNELS,
                        'route' => Module::ROUTE.'/'.ChannelController::ROUTE.'/'.ChannelController::ROUTE_CHANNELS,
                        'params' => [
                            'type' => Type::SALES
                        ]
                    ],
                    Type::SHIPPING . ' ' . ChannelController::ROUTE_CHANNELS => [
                        'label' => ucwords(Type::SHIPPING) . ' ' . ChannelController::ROUTE_CHANNELS,
                        'title' => ucwords(Type::SHIPPING) . ' ' . ChannelController::ROUTE_CHANNELS,
                        'route' => Module::ROUTE.'/'.ChannelController::ROUTE.'/'.ChannelController::ROUTE_CHANNELS,
                        'params' => [
                            'type' => Type::SHIPPING
                        ]
                    ],
                ]
            ],
            'Invoices' => [
                'label' => 'Invoice Management',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    [
                        'label' => InvoiceController::ROUTE_MAPPING,
                        'title' => InvoiceController::ROUTE_MAPPING,
                        'route' => Module::ROUTE.'/'.InvoiceController::ROUTE.'/'.InvoiceController::ROUTE_MAPPING
                    ], [
                        'label' => InvoiceController::ROUTE_DESIGNER,
                        'title' => InvoiceController::ROUTE_DESIGNER,
                        'route' => Module::ROUTE.'/'.InvoiceController::ROUTE.'/'.InvoiceController::ROUTE_DESIGNER
                    ],
                ]
            ],
            'Shipping Management' => [
                'label' => 'Shipping Management',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    [
                        'label' => ShippingController::ROUTE_ALIASES,
                        'title' => ShippingController::ROUTE_ALIASES,
                        'route' => Module::ROUTE . '/' . ShippingController::ROUTE . '/' . ShippingController::ROUTE_ALIASES
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
                'type' => Literal::class,
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
                        'type' => Literal::class,
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
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/:type',
                                    'defaults' => [
                                        'controller' => ChannelController::class,
                                        'action' => 'list',
                                    ],
                                    'constraints' => [
                                        'type' => implode('|', Type::getTypes())
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'Sales Channel Ebay' => [
                                        'type' => Literal::class,
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
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/amazon/:region',
                                            'defaults' => [
                                                'controller' => AmazonController::class,
                                                'action' => 'save'
                                            ]
                                        ],
                                        'may_terminate' => true
                                    ],
                                    ChannelController::ROUTE_AJAX => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/ajax',
                                            'defaults' => [
                                                'action' => 'listAjax',
                                            ]
                                        ],
                                    ],
                                    ChannelController::ROUTE_CREATE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/create',
                                            'defaults' => [
                                                'action' => 'create'
                                            ]
                                        ],
                                        'may_terminate' => true
                                    ],
                                    ChannelController::ROUTE_ACCOUNT => [
                                        'type' => Segment::class,
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
                                            ChannelController::ROUTE_ACCOUNT_STATUS => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/enable',
                                                    'defaults' => [
                                                        'action' => 'statusAjax',
                                                    ]
                                                ],
                                            ],
                                            ChannelController::ROUTE_ACCOUNT_AJAX => [
                                                'type' => Segment::class,
                                                'options' => [
                                                    'route' => '/ajax',
                                                    'defaults' => [
                                                        'action' => 'accountUpdate'
                                                    ],
                                                ],
                                            ],
                                            ChannelController::ROUTE_ACCOUNT_DELETE => [
                                                'type' => Literal::class,
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
                        'type' => Literal::class,
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
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/mapping',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'mapping',
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    InvoiceController::ROUTE_SAVE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/save',
                                            'defaults' => [
                                                'controller' => InvoiceController::class,
                                                'action' => 'saveMapping',
                                            ]
                                        ]
                                    ],
                                    InvoiceController::ROUTE_AJAX => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/ajax',
                                            'defaults' => [
                                                'controller' => InvoiceController::class,
                                                'action' => 'ajaxMapping',
                                            ]
                                        ]
                                    ],
                                ]
                            ],
                            InvoiceController::ROUTE_DESIGNER => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/designer',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'design',
                                    ]
                                ],
                            ],
                            InvoiceController::ROUTE_FETCH => [
                                'type' => Literal::class,
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
                                'type' => Literal::class,
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
                    ],
                    ShippingController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/shipping',
                            'defaults' => [
                                'controller' => ShippingController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            ShippingController::ROUTE_ALIASES => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/alias',
                                    'defaults' => [
                                        'action' => 'alias',
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    ShippingController::ROUTE_ALIASES_SAVE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/save',
                                            'defaults' => [
                                                'action' => 'aliasSave',
                                            ]
                                        ],
                                    ],
                                    ShippingController::ROUTE_ALIASES_REMOVE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/delete',
                                            'defaults' => [
                                                'action' => 'aliasDelete',
                                            ]
                                        ],
                                    ]
                                ]
                            ],
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
                'InvoiceSettingsDataTable' => DataTable::class,
                'salesAccountList' => DataTable::class,
                'shippingAccountList' => DataTable::class,
                'InvoiceSettingsDataTableSettings' => DataTable\Settings::class,
                'AccountListSettings' => DataTable\Settings::class,
                'ChannelTokenStatusMustacheJS' => ViewModel::class,
                'ChannelStatusJS' => viewModel::class,
                'ChannelDeleteJavascript' => ViewModel::class,
                'InvoiceTradingCompanyColumn' => DataTable\Column::class,
                'InvoiceAssignedInvoiceColumn' => DataTable\Column::class,
                'InvoiceTradingCompanyColumnView' => ViewModel::class,
                'InvoiceAssignedInvoiceColumnView' => ViewModel::class,
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
            InvoiceController::class => [
                'parameters' => [
                    'config' => 'app_config'
                ]
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
            InvoiceService::class => [
                'parameters' => [
                    'datatable' => 'InvoiceSettingsDataTable',
                ],
            ],
            'InvoiceSettingsDataTable' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'accounts'
                    ],
                ],
                'injections' => [
                    'addChild' => [
                        ['child' => 'ChannelTokenStatusMustacheJS', 'captureTo' => 'javascript', 'append' => true],
                    ],
                    'addColumn' => [
                        ['column' => 'InvoiceTradingCompanyColumn'],
                        ['column' => 'InvoiceAssignedInvoiceColumn'],
                    ],
                    'setVariable' => [
                        ['name' => 'settings', 'value' => 'InvoiceSettingsDataTableSettings']
                    ],
                ]
            ],
            'InvoiceSettingsDataTableSettings' => [
                'parameters' => [
                    'scrollHeightAuto' => true,
                    'footer' => false,
                ]
            ],
            'salesAccountList' => [
                'parameters' => [
                    'variables' => [
                        'sortable' => 'false',
                        'id' => 'accounts'
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
            'shippingAccountList' => [
                'parameters' => [
                    'variables' => [
                        'sortable' => 'false',
                        'id' => 'accounts'
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
                    'tableOptions' => 'rt<"table-footer" pil <"auto-refresh">>'
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
                                ChannelController::ROUTE_ACCOUNT,
                                ChannelController::ROUTE_ACCOUNT_STATUS,
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

            'InvoiceTradingCompanyColumn' => [
                'parameters' => [
                    'templateId' => 'tradingCompany',
                    'viewModel' => 'InvoiceTradingCompanyColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '100px',
                ],
            ],
            'InvoiceAssignedInvoiceColumn' => [
                'parameters' => [
                    'templateId' => 'assignedInvoice',
                    'viewModel' => 'InvoiceAssignedInvoiceColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '100px',
                ],
            ],
            'InvoiceTradingCompanyColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Trading Company'],
                    'template' => 'value.phtml',
                ],
            ],
            'InvoiceAssignedInvoiceColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Assigned Invoice'],
                    'template' => 'value.phtml',
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
                    'hideable' => false
                ],
            ],
            'AccountTokenStatusColumn' => [
                'parameters' => [
                    'templateId' => 'tokenStatus',
                    'viewModel' => 'AccountTokenStatusColumnView',
                    'sortable' => false,
                    'hideable' => false
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
                    'variables' => ['value' => 'Channel'],
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
                    'variables' => ['value' => 'Connection Expires'],
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
            ],
            ShippingMethodService::class => [
                'parameters' => [
                    'repository' => ShippingMethodStorage::class
                ]
            ],
            ShippingMethodStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
            ShippingAliasService::class => [
                'parameters' => [
                    'repository' => ShippingAliasStorage::class
                ]
            ],
            ShippingAliasStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
            AmazonController::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor'
                ]
            ],
            EbayController::class => [
                'parameters' => [
                    'cryptor' => 'ebay_cryptor'
                ]
            ],
            EbayAccount::class => [
                'parameters' => [
                    'cryptor' => 'ebay_cryptor'
                ]
            ],
            AmazonAccount::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor'
                ]
            ]
        ]
    ]
];
