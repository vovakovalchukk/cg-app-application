<?php
use CG\Account\Client\Service as AccountService;
use CG\Account\Client\Storage\Api as AccountStorage;
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\Account\Client\StorageInterface as AccountStorageInterface;
use CG\Amazon\Account as AmazonAccount;
use CG\Amazon\Account\CreationService as AmazonAccountCreationService;
use CG\Amazon\Marketplace\Participation\Service as MarketplaceParticipationService;
use CG\Channel\Type;
use CG\Ebay\Account as EbayAccount;
use CG\Ebay\Account\CreationService as EbayAccountCreationService;
use CG\Ebay\Client\TradingApi;
use CG\Ekm\Account as EkmAccount;
use CG\Ekm\Account\CreationService as EkmAccountCreationService;
use CG\Log\Logger;
use CG\Order\Client\Shipping\Method\Storage\Api as ShippingMethodStorage;
use CG\Order\Service\Shipping\Method\Service as ShippingMethodService;
use CG\OrganisationUnit\Storage\Api as OUApiStorage;
use CG\OrganisationUnit\StorageInterface as OUStorageInterface;
use CG\Settings\PickList\Service as PickListService;
use CG\Settings\PickList\Storage\Api as PickListStorage;
use CG\Settings\Shipping\Alias\Service as ShippingAliasService;
use CG\Settings\Shipping\Alias\Storage\Api as ShippingAliasStorage;
use CG\Stdlib\Log\LoggerInterface;
use CG\Template\Repository as TemplateRepository;
use CG\Template\Service as TemplateService;
use CG\Template\Storage\Api as TemplateApiStorage;
use CG\WooCommerce\Account as WooCommerceAccount;
use CG\WooCommerce\Account\CreationService as WooCommerceAccountCreationService;
use CG\WooCommerce\Client\Factory as WooCommerceClientFactory;
use CG_UI\View\DataTable;
use Guzzle\Http\Client as GuzzleHttpClient;
use Orders\Order\Invoice\Template\ObjectStorage as TemplateObjectStorage;
use Settings\Controller\AdvancedController;
use Settings\Controller\AmazonController;
use Settings\Controller\ApiController;
use Settings\Controller\ChannelController;
use Settings\Controller\EbayController;
use Settings\Controller\EkmController;
use Settings\Controller\ExportController;
use Settings\Controller\IndexController;
use Settings\Controller\InvoiceController;
use Settings\Controller\PickListController;
use Settings\Controller\ShippingController;
use Settings\Controller\StockController;
use Settings\Controller\StockJsonController;
use Settings\Controller\WooCommerceController;
use Settings\Factory\SidebarNavFactory;
use Settings\Invoice\Service as InvoiceService;
use Settings\Module;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;
use Zend\View\Model\ViewModel;

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
            ],
            'Picking Management' => [
                'label' => 'Picking Management',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    [
                        'label' => PickListController::ROUTE_PICK_LIST,
                        'title' => PickListController::ROUTE_PICK_LIST,
                        'route' => Module::ROUTE . '/' . PickListController::ROUTE . '/' . PickListController::ROUTE_PICK_LIST
                    ]
                ]
            ],
            'Product Management' => [
                'label' => 'Product Management',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    [
                        'label' => StockController::ROUTE,
                        'title' => PickListController::ROUTE,
                        'route' => Module::ROUTE . '/' . StockController::ROUTE,
                    ],
                ]
            ],
            'Advanced' => [
                'label' => 'Advanced',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    [
                        'label' => ApiController::ROUTE_API,
                        'title' => ApiController::ROUTE_API,
                        'route' => Module::ROUTE . '/' . AdvancedController::ROUTE . '/' . ApiController::ROUTE_API
                    ],
                    [
                        'label' => ExportController::ROUTE_EXPORT,
                        'title' => ExportController::ROUTE_EXPORT,
                        'route' => Module::ROUTE . '/' . AdvancedController::ROUTE . '/' . ExportController::ROUTE_EXPORT
                    ]
                ]
            ]
        ],
        'application-navigation' => [
            'settings' => [
                'label'  => 'Settings',
                'sprite' => 'sprite-settings-18-white',
                'order'  => 20,
                'uri'    => 'https://' . $_SERVER['HTTP_HOST'] . '/settings'
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
                                    EkmAccount::ROUTE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/ekm',
                                            'defaults' => [
                                                'controller' => EkmController::class,
                                                'action' => 'index',
                                                'sidebar' => false
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            EkmController::ROUTE_AJAX => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/ajax',
                                                    'defaults' => [
                                                        'action' => 'save',
                                                    ],
                                                ],
                                            ]
                                        ]
                                    ],
                                    WooCommerceAccount::ROUTE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/woocommerce',
                                            'defaults' => [
                                                'controller' => WooCommerceController::class,
                                                'action' => 'index',
                                                'sidebar' => false
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            WooCommerceController::ROUTE_AJAX => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/ajax',
                                                    'defaults' => [
                                                        'action' => 'save',
                                                    ],
                                                ],
                                            ]
                                        ]
                                    ],
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
                                            ChannelController::ROUTE_ACCOUNT_STOCK_MANAGEMENT => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/stockManagement',
                                                    'defaults' => [
                                                        'action' => 'stockManagementAjax',
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
                            ShippingController::ROUTE_SERVICES => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/services/:account',
                                    'defaults' => [
                                        'action' => 'getServices'
                                    ]
                                ],
                                'constraints' => [
                                    'account' => '[0-9]*'
                                ],
                                'may_terminate' => true
                            ]
                        ]
                    ],
                    PickListController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/picking',
                            'defaults' => [
                                'controller' => PickListController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            PickListController::ROUTE_PICK_LIST => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'action' => 'pickList'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    PickListController::ROUTE_PICK_LIST_SAVE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/save',
                                            'defaults' => [
                                                'action' => 'save'
                                            ]
                                        ],
                                        'may_terminate' => true
                                    ]
                                ]
                            ]
                        ]
                    ],
                    AdvancedController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/advanced',
                            'defaults' => [
                                'controller' => AdvancedController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            ApiController::ROUTE_API => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/api',
                                    'defaults' => [
                                        'controller' => ApiController::class,
                                        'action' => 'details'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            ExportController::ROUTE_EXPORT => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/export',
                                    'defaults' => [
                                        'controller' => ExportController::class,
                                        'action' => 'export'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    ExportController::ROUTE_EXPORT_ORDER => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/orders',
                                            'defaults' => [
                                                'action' => 'exportOrder'
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                    ExportController::ROUTE_EXPORT_ORDER_ITEM => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/orderItems',
                                            'defaults' => [
                                                'action' => 'exportOrderItem'
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                ],
                            ]
                        ]
                    ],
                    StockController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => StockController::ROUTE_URI,
                            'defaults' => [
                                'controller' => StockController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            StockJsonController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => StockJsonController::ROUTE_SAVE_URI,
                                    'defaults' => [
                                        'controller' => StockJsonController::class,
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ]
                    ],
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
                'ChannelStatusJS' => ViewModel::class,
                'ChannelStockManagementJS' => ViewModel::class,
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
                'AccountStockManagementColumn' => DataTable\Column::class,
                'AccountEnableColumnView' => ViewModel::class,
                'AccountStatusColumnView' => ViewModel::class,
                'AccountChannelColumnView' => ViewModel::class,
                'AccountAccountColumnView' => ViewModel::class,
                'AccountTradingCompanyColumnView' => ViewModel::class,
                'AccountTokenStatusColumnView' => ViewModel::class,
                'AccountManageColumnView' => ViewModel::class,
                'AccountStockManagementColumnView' => ViewModel::class
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
                        ['child' => 'ChannelStockManagementJS', 'captureTo' => 'javascript', 'append' => true],
                        ['child' => 'ChannelDeleteJavascript', 'captureTo' => 'javascript', 'append' => true],
                    ],
                    'addColumn' => [
                        ['column' => 'AccountEnableColumn'],
                        ['column' => 'AccountStatusColumn'],
                        ['column' => 'AccountChannelColumn'],
                        ['column' => 'AccountAccountColumn'],
                        ['column' => 'AccountTradingCompanyColumn'],
                        ['column' => 'AccountTokenStatusColumn'],
                        ['column' => 'AccountStockManagementColumn'],
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
                    'template' => 'settings/channel/javascript/Switch.js',
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
                        'switchClass' => 'enable_switch',
                        'switchType' => 'Status'
                    ],
                ],
            ],
            'ChannelDeleteJavascript' => [
                'parameters' => [
                    'template' => 'settings/channel/javascript/deleteChannel.js',
                ],
            ],
            'ChannelStockManagementJS' => [
                'parameters' => [
                    'template' => 'settings/channel/javascript/Switch.js',
                    'variables' => [
                        'route' => implode(
                            '/',
                            [
                                Module::ROUTE,
                                ChannelController::ROUTE,
                                ChannelController::ROUTE_CHANNELS,
                                ChannelController::ROUTE_ACCOUNT,
                                ChannelController::ROUTE_ACCOUNT_STOCK_MANAGEMENT,
                            ]
                        ),
                        'switchClass' => 'stockManagement_switch',
                        'switchType' => 'Stock Management'
                    ],
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
            'AccountStockManagementColumn' => [
                'parameters' => [
                    'templateId' => 'stockManagement',
                    'viewModel' => 'AccountStockManagementColumnView',
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
            'AccountStockManagementColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Stock Management'],
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
            WooCommerceController::class => [
                'parameters' => [
                    'accountCreationService' => WooCommerceAccountCreationService::class
                ],
            ],
            EkmController::class => [
                'parameters' => [
                    'accountCreationService' => EkmAccountCreationService::class
                ]
            ],
            EbayController::class => [
                'parameters' => [
                    'accountCreationService' => EbayAccountCreationService::class
                ]
            ],
            AmazonController::class => [
                'parameters' => [
                    'accountCreationService' => AmazonAccountCreationService::class
                ]
            ],
            WooCommerceAccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'woocommerce_cryptor',
                    'channelAccount' => WooCommerceAccount::class
                ]
            ],
            EkmAccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'ekm_cryptor',
                    'channelAccount' => EkmAccount::class
                ]
            ],
            EbayAccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'ebay_cryptor',
                    'channelAccount' => EbayAccount::class
                ]
            ],
            AmazonAccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor'
                ]
            ],
            AmazonAccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor'
                ]
            ],
            MarketplaceParticipationService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor'
                ]
            ],
            EbayAccount::class => [
                'parameters' => [
                    'cryptor' => 'ebay_cryptor'
                ]
            ],
            PickListStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            PickListService::class => [
                'parameters' => [
                    'repository' => PickListStorage::class
                ]
            ],
            WooCommerceClientFactory::class => [
                'parameters' => [
                    'cryptor' => 'woocommerce_cryptor',
                    'guzzle' => function() { return 'woocommerce_guzzle'; },
                ]
            ],
        ]
    ]
];
