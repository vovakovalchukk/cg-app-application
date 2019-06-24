<?php

use CG\Account\Client\Service as AccountService;
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\Account\Client\Storage\Api as AccountStorage;
use CG\Account\Client\StorageInterface as AccountStorageInterface;
use CG\Amazon\Account as AmazonAccount;
use CG\Amazon\Account\CreationService as AmazonAccountCreationService;
use CG\Amazon\Marketplace\Participation\Service as MarketplaceParticipationService;
use CG\Channel\Type;
use CG\Ebay\Account as EbayAccount;
use CG\Ebay\Account\CreationService as EbayAccountCreationService;
use CG\Ekm\Account as EkmAccount;
use CG\Ekm\Account\CreationService as EkmAccountCreationService;
use CG\FileStorage\S3\Adapter as S3Adapter;
use CG\Listing\Csv\Storage\S3 as ListingsCsvStorageS3;
use CG\Listing\Csv\StorageInterface as ListingsCsvStorage;
use CG\Log\Logger;
use CG\Order\Client\Shipping\Method\Storage\Api as ShippingMethodApiStorage;
use CG\Order\Client\Shipping\Method\Storage\Cache as ShippingMethodCacheStorage;
use CG\Order\Service\Shipping\Method\Service as ShippingMethodService;
use CG\Product\Client\Service as ProductService;
use Settings\ListingTemplate\Service as ListingTemplateService;
use CG\Settings\PickList\Service as PickListService;
use CG\Settings\PickList\Storage\Api as PickListStorage;
use CG\Settings\Shipping\Alias\Service as ShippingAliasService;
use CG\Settings\Shipping\Alias\Storage\Api as ShippingAliasStorage;
use CG\ShipStation\Account as ShipStationAccount;
use CG\ShipStation\Account\CreationService as ShipStationCreationService;
use CG\Stdlib\Log\LoggerInterface;
use CG\Template\Repository as TemplateRepository;
use CG\Template\Service as TemplateService;
use CG\Template\Storage\Api as TemplateApiStorage;
use CG\WooCommerce\Account as WooCommerceAccount;
use CG\WooCommerce\Account\CreationService as WooCommerceAccountCreationService;
use CG\WooCommerce\Client\Factory as WooCommerceClientFactory;
use CG_NetDespatch\Account\CreationService as AccountCreationService;
use CG_Permission\Service as PermissionService;
use CG_UI\View\DataTable;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Order\Invoice\Template\ObjectStorage as TemplateObjectStorage;
use Settings\Controller\AdvancedController;
use Settings\Controller\AmazonController;
use Settings\Controller\ApiController;
use Settings\Controller\CategoryTemplatesController;
use Settings\Controller\CategoryTemplatesJsonController;
use Settings\Controller\ListingTemplatesController;
use Settings\Controller\ChannelController;
use Settings\Controller\ListingController;
use Settings\Controller\CreateListingsController;
use Settings\Controller\EbayController;
use Settings\Controller\EkmController;
use Settings\Controller\EmailController;
use Settings\Controller\ExportController;
use Settings\Controller\IndexController;
use Settings\Controller\InvoiceController;
use Settings\Controller\OrderController;
use Settings\Controller\PickListController;
use Settings\Controller\ShippingController;
use Settings\Controller\ShippingLedgerController;
use Settings\Controller\StockController;
use Settings\Controller\StockJsonController;
use Settings\Controller\WooCommerceController;
use Settings\Factory\SidebarNavFactory;
use Settings\Invoice\Mappings as InvoiceMappings;
use Settings\Invoice\Settings as InvoiceSettings;
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
    'SetupWizard' => [
        'SetupWizard' => [
            'white_listed_routes' => [
                Module::ROUTE . '/' . ChannelController::ROUTE . '/' . ChannelController::ROUTE_CHANNELS . '/' . EkmAccount::ROUTE . '/' . EkmController::ROUTE_AJAX => true,
                Module::ROUTE . '/' . ChannelController::ROUTE . '/' . ChannelController::ROUTE_CHANNELS . '/' . WooCommerceAccount::ROUTE . '/' . WooCommerceController::ROUTE_AJAX => true,
                Module::ROUTE . '/' . InvoiceController::ROUTE . '/' . InvoiceController::ROUTE_SETTINGS . '/' . InvoiceController::ROUTE_SAVE,
            ]
        ]
    ],
    'navigation' => [
        'settings-navigation' => [
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
                        'label' => InvoiceController::ROUTE_SETTINGS,
                        'title' => InvoiceController::ROUTE_SETTINGS,
                        'route' => Module::ROUTE.'/'.InvoiceController::ROUTE.'/'.InvoiceController::ROUTE_SETTINGS
                    ], [
                        'label' => InvoiceController::ROUTE_DESIGNER,
                        'title' => InvoiceController::ROUTE_DESIGNER,
                        'route' => Module::ROUTE.'/'.InvoiceController::ROUTE
//                    ], [
//                        'label' => EmailController::ROUTE_DESIGNER,
//                        'title' => EmailController::ROUTE_DESIGNER,
//                        'route' => Module::ROUTE.'/'.EmailController::ROUTE.'/'.EmailController::ROUTE_DESIGNER,
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
            'Order Management' => [
                'label' => 'Order Management',
                'uri' => '',
                'class' => 'heading-medium',
                'pages' => [
                    [
                        'label' => 'Orders',
                        'title' => 'Order settings',
                        'route' => Module::ROUTE . '/' . OrderController::ROUTE
                    ],
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
                        'title' => 'Stock settings',
                        'route' => Module::ROUTE . '/' . StockController::ROUTE,
                    ],
                    [
                        'label' => 'Create Listings',
                        'title' => 'Create channel listings from imported data',
                        'route' => Module::ROUTE . '/' . CreateListingsController::ROUTE,
                        'feature-flag' => ProductService::FEATURE_FLAG_PRODUCT_EXPORT
                    ],
                    [
                        'label' => 'Category Templates',
                        'title' => 'Manage the category templates',
                        'route' => Module::ROUTE . '/Category/' . CategoryTemplatesController::ROUTE_INDEX
                    ],
                    [
                        'label' => 'Listing Templates',
                        'title' => 'Manage the listing templates',
                        'route' => Module::ROUTE . '/Listing/' . ListingTemplatesController::ROOT_INDEX,
                        'feature-flag' => ListingTemplateService::FEATURE_FLAG
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
            'settings-navigation'  => SidebarNavFactory::class,
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
                    'Listing' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/listing'
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            ListingTemplatesController::ROOT_INDEX => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/templates',
                                    'defaults' => [
                                        'controller' => ListingTemplatesController::class,
                                        'action' => 'index',
                                    ]
                                ]
                            ],
                            ListingTemplatesController::SAVE_INDEX => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'controller' => ListingTemplatesController::class,
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            ListingTemplatesController::DELETE_INDEX => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'controller' => ListingTemplatesController::class,
                                        'action' => 'delete'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                            ListingTemplatesController::PREVIEW_INDEX => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/preview',
                                    'defaults' => [
                                        'controller' => ListingTemplatesController::class,
                                        'action' => 'preview'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
                        ]
                    ],
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
                                                'action' => 'save',
                                                PermissionService::PARTNER_MANAGED_ROUTE_WHITELIST => true
                                            ]
                                        ],
                                        'child_routes' => [
                                            'oauth' => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/oauth',
                                                    'defaults' => [
                                                        'controller' => EbayController::class,
                                                        'action' => 'saveOAuth',
                                                    ],
                                                ],
                                                'child_routes' => [
                                                    'checkOAuth' => [
                                                        'type' => Segment::class,
                                                        'options' => [
                                                            'route' => '/:accountId',
                                                            'defaults' => [
                                                                'controller' => EbayController::class,
                                                                'action' => 'checkOAuth',
                                                            ],
                                                            'constraints' => [
                                                                'accountId' => '[0-9]*'
                                                            ],
                                                        ],
                                                        'may_terminate' => true
                                                    ]
                                                ],
                                                'may_terminate' => true
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
                                                'action' => 'save',
                                                PermissionService::PARTNER_MANAGED_ROUTE_WHITELIST => true
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
                                            ChannelController::ROUTE_ACCOUNT_STOCK_MANAGEMENT => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/stockManagement',
                                                    'defaults' => [
                                                        'action' => 'stockManagementAjax',
                                                    ]
                                                ],
                                            ],
                                            ChannelController::ROUTE_ACCOUNT_AUTO_LISTINGS_IMPORT => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/autoImportListings',
                                                    'defaults' => [
                                                        'action' => 'autoImportListingsAjax',
                                                    ]
                                                ],
                                            ],
                                            ShippingLedgerController::ROUTE => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/ledger',
                                                    'defaults' => [
                                                        'controller' => ShippingLedgerController::class,
                                                    ]
                                                ],
                                                'child_routes' => [
                                                    ShippingLedgerController::ROUTE_TOPUP => [
                                                        'type' => Literal::class,
                                                        'options' => [
                                                            'route' => '/topup',
                                                            'defaults' => [
                                                                'action' => 'topup',
                                                            ]
                                                        ],
                                                    ],
                                                    ShippingLedgerController::ROUTE_SAVE => [
                                                        'type' => Literal::class,
                                                        'options' => [
                                                            'route' => '/save',
                                                            'defaults' => [
                                                                'action' => 'save',
                                                            ]
                                                        ],
                                                    ],
                                                ]
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
                            InvoiceController::ROUTE_SETTINGS => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/settings',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'settings',
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
                                                'action' => 'saveSettings',
                                            ]
                                        ]
                                    ],
                                    InvoiceController::ROUTE_SAVE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/delete',
                                            'defaults' => [
                                                'controller' => InvoiceController::class,
                                                'action' => 'saveSettings',
                                            ]
                                        ]
                                    ],
                                    InvoiceController::ROUTE_AJAX => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/ajaxSettings',
                                            'defaults' => [
                                                'controller' => InvoiceController::class,
                                                'action' => 'ajaxSettings',
                                            ]
                                        ]
                                    ],
                                    InvoiceController::ROUTE_SAVE_MAPPING => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/saveMapping',
                                            'defaults' => [
                                                'controller' => InvoiceController::class,
                                                'action' => 'saveMapping',
                                            ]
                                        ]
                                    ],
                                    InvoiceController::ROUTE_AJAX_MAPPING => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/ajaxMapping',
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
                                'may_terminate' => true,
                                'child_routes' => [
                                    InvoiceController::ROUTE_DESIGNER_ID => [
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/id/:templateId',
                                        ],
                                        'may_terminate' => true
                                    ],
                                ],
                            ],
                            InvoiceController::ROUTE_TEMPLATES => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/templates',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'existingInvoiceTemplates'
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
                            ],
                            InvoiceController::ROUTE_DELETE_TEMPLATE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/deleteTemplate',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'deleteTemplate'
                                    ]
                                ],
                                'may_terminate' => true
                            ],
                            InvoiceController::ROUTE_ADD_FAVOURITE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/addFavourite',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'addFavourite'
                                    ]
                                ],
                                'may_terminate' => true
                            ],
                            InvoiceController::ROUTE_REMOVE_FAVOURITE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/removeFavourite',
                                    'defaults' => [
                                        'controller' => InvoiceController::class,
                                        'action' => 'removeFavourite'
                                    ]
                                ],
                                'may_terminate' => true
                            ]
                        ]
                    ],
                    EmailController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/email',
                            'defaults' => [
                                'controller' => EmailController::class,
                                'action' => 'design',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            EmailController::ROUTE_DESIGNER => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/designer',
                                    'defaults' => [
                                        'controller' => EmailController::class,
                                        'action' => 'design',
                                        'sidebar' => false,
                                    ],
                                ],
                            ],
                        ],
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
                            ],
                            ShippingController::ROUTE_SERVICE_OPTIONS => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/serviceOptions/:account',
                                    'defaults' => [
                                        'action' => 'getServiceOptions'
                                    ]
                                ],
                                'constraints' => [
                                    'account' => '[0-9]*'
                                ],
                                'may_terminate' => true
                            ]
                        ]
                    ],
                    OrderController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/orders',
                            'defaults' => [
                                'controller' => OrderController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            OrderController::ROUTE_SAVE => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/save',
                                    'defaults' => [
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true,
                            ],
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
                                        'child_routes' => [
                                            ExportController::ROUTE_EXPORT_ORDER_CHECK => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/check',
                                                    'defaults' => [
                                                        'action' => 'exportCheck'
                                                    ]
                                                ],
                                                'may_terminate' => true,
                                            ],
                                            ExportController::ROUTE_EXPORT_ORDER_PROGRESS => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/progress',
                                                    'defaults' => [
                                                        'action' => 'exportProgress'
                                                    ]
                                                ],
                                                'may_terminate' => true,
                                            ],
                                        ],
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
                                        'child_routes' => [
                                            ExportController::ROUTE_EXPORT_ORDER_ITEM_CHECK => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/check',
                                                    'defaults' => [
                                                        'action' => 'exportCheck'
                                                    ]
                                                ],
                                                'may_terminate' => true,
                                            ],
                                            ExportController::ROUTE_EXPORT_ORDER_ITEM_PROGRESS => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/progress',
                                                    'defaults' => [
                                                        'action' => 'exportProgress'
                                                    ]
                                                ],
                                                'may_terminate' => true,
                                            ],
                                        ],
                                    ],
                                    ExportController::ROUTE_EXPORT_PRODUCT => [
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/products/[:channel]',
                                            'defaults' => [
                                                'controller' => ExportController::class,
                                                'action' => 'exportProduct'
                                            ]
                                        ],
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
                            StockJsonController::ROUTE_ACCOUNTS => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => StockJsonController::ROUTE_ACCOUNTS_URI,
                                    'defaults' => [
                                        'controller' => StockJsonController::class,
                                        'action' => 'accountsList'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    StockJsonController::ROUTE_ACCOUNTS_SAVE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => StockJsonController::ROUTE_ACCOUNTS_SAVE_URI,
                                            'defaults' => [
                                                'controller' => StockJsonController::class,
                                                'action' => 'accountsSave'
                                            ]
                                        ],
                                        'may_terminate' => true,
                                    ],
                                ]
                            ],
                        ]
                    ],
                    CreateListingsController::ROUTE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/createListings',
                            'defaults' => [
                                'controller' => CreateListingsController::class,
                                'action' => 'index'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            CreateListingsController::ROUTE_IMPORT => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/import',
                                    'defaults' => [
                                        'action' => 'import'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => []
                            ],
                        ]
                    ],
                    'Category' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/category',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            CategoryTemplatesController::ROUTE_INDEX => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/templates',
                                    'defaults' => [
                                        'action' => 'index',
                                        'controller' => CategoryTemplatesController::class
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    CategoryTemplatesJsonController::ROUTE_ACCOUNTS => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/accounts',
                                            'defaults' => [
                                                'action' => 'accounts',
                                                'controller' => CategoryTemplatesJsonController::class
                                            ]
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => []
                                    ],
                                    CategoryTemplatesJsonController::ROUTE_FETCH => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/fetch',
                                            'defaults' => [
                                                'action' => 'fetch',
                                                'controller' => CategoryTemplatesJsonController::class
                                            ]
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => []
                                    ],
                                    CategoryTemplatesJsonController::ROUTE_CATEGORY_ROOTS => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/category-roots',
                                            'defaults' => [
                                                'action' => 'categoryRoots',
                                                'controller' => CategoryTemplatesJsonController::class
                                            ]
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => []
                                    ],
                                    CategoryTemplatesJsonController::ROUTE_SAVE => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/save',
                                            'defaults' => [
                                                'action' => 'save',
                                                'controller' => CategoryTemplatesJsonController::class
                                            ]
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => []
                                    ],
                                    CategoryTemplatesJsonController::ROUTE_CATEGORY_CHILDREN => [
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/:accountId/category-children/:categoryId',
                                            'defaults' => [
                                                'action' => 'categoryChildren',
                                                'controller' => CategoryTemplatesJsonController::class
                                            ],
                                            'constraints' => [
                                                'accountId' => '[0-9]*',
                                                'categoryId' => '[0-9]*'
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => []
                                    ],
                                    CategoryTemplatesJsonController::ROUTE_REFRESH_CATEGORIES => [
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/:accountId/refresh-categories',
                                            'defaults' => [
                                                'action' => 'refreshCategories',
                                                'controller' => CategoryTemplatesJsonController::class
                                            ],
                                            'constraints' => [
                                                'accountId' => '[0-9]*'
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => []
                                    ],
                                    CategoryTemplatesJsonController::ROUTE_TEMPLATE_DELETE => [
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/:templateId/delete',
                                            'defaults' => [
                                                'action' => 'templateDelete',
                                                'controller' => CategoryTemplatesJsonController::class
                                            ],
                                            'constraints' => [
                                                'templateId' => '[0-9]*'
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => []
                                    ],
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
            StockController::ACCOUNT_SETTINGS_TABLE_TEMPLATE => dirname(__DIR__) . '/view/settings/stock/accountStockSettingsTable.phtml',
        ]
    ],
    'di' => [
        'instance' => [
            'preferences' => [
                AccountStorageInterface::class => AccountApiStorage::class,
                LoggerInterface::class => Logger::class,
                ListingsCsvStorage::class => ListingsCsvStorageS3::class,
            ],
            'aliases' => [
                'InvoiceSettingsDataTable' => DataTable::class,
                'salesAccountList' => DataTable::class,
                'shippingAccountList' => DataTable::class,
                'InvoiceSettingsDataTableSettings' => DataTable\Settings::class,
                'InvoiceMappingDatatable' => DataTable::class,
                'InvoiceMappingDatatableSettings' => DataTable\Settings::class,
                'AccountListSettings' => DataTable\Settings::class,
                'ChannelTokenStatusMustacheJS' => ViewModel::class,
                'ChannelStatusJS' => ViewModel::class,
                'ChannelStockManagementJS' => ViewModel::class,
                'ChannelDeleteJavascript' => ViewModel::class,
                'InvoiceTradingCompanyColumn' => DataTable\Column::class,
                'InvoiceAssignedInvoiceColumn' => DataTable\Column::class,
                'InvoiceSendFromAddressColumn' => DataTable\Column::class,
                'InvoiceTradingCompanyColumnView' => ViewModel::class,
                'InvoiceAssignedInvoiceColumnView' => ViewModel::class,
                'InvoiceSendFromAddressColumnView' => ViewModel::class,
                'InvoiceMappingChannelColumn' => DataTable\Column::class,
                'InvoiceMappingDisplayNameColumn' => DataTable\Column::class,
                'InvoiceMappingSiteColumn' => DataTable\Column::class,
                'InvoiceMappingTradingCompanyColumn' => DataTable\Column::class,
                'InvoiceMappingAssignedInvoiceColumn' => DataTable\Column::class,
                'InvoiceMappingEmailContentColumn' => DataTable\Column::class,
                'InvoiceMappingSendViaEmailColumn' => DataTable\Column::class,
                'InvoiceMappingSendToFbaColumn' => DataTable\Column::class,
                'InvoiceMappingChannelColumnView' => ViewModel::class,
                'InvoiceMappingDisplayNameColumnView' => ViewModel::class,
                'InvoiceMappingSiteColumnView' => ViewModel::class,
                'InvoiceMappingTradingCompanyColumnView' => ViewModel::class,
                'InvoiceMappingAssignedInvoiceColumnView' => ViewModel::class,
                'InvoiceMappingEmailContentColumnView' => ViewModel::class,
                'InvoiceMappingSendViaEmailColumnView' => ViewModel::class,
                'InvoiceMappingSendToFbaColumnView' => ViewModel::class,
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
                'AccountStockManagementColumnView' => ViewModel::class,

                'ListingsCsvS3Adapter' => S3Adapter::class,
            ],
            InvoiceController::class => [
                'parameters' => [
                    'config' => 'app_config',
                    'amazonCryptor' => 'amazon_cryptor',
                ]
            ],
            EmailController::class => [
                'parameters' => [
                    'config' => 'app_config'
                ]
            ],
            AccountApiStorage::class => [
                'parameters' => [
                    'client' => 'account_guzzle',
                ],
            ],
            InvoiceSettings::class => [
                'parameters' => [
                    'datatable' => 'InvoiceSettingsDataTable',
                ],
            ],
            InvoiceMappings::class => [
                'parameters' => [
                    'datatable' => 'InvoiceMappingDatatable',
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
                        ['column' => 'InvoiceSendFromAddressColumn'],
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
            'InvoiceMappingDatatable' => [
                'parameters' => [
                    'variables' => [
                        'sortable' => false,
                        'class' => 'fixed-header fixed-footer',
                        'id' => 'invoiceMapping'
                    ],
                ],
                'injections' => [
                    'addColumn' => [
                        ['column' => 'InvoiceMappingChannelColumn'],
                        ['column' => 'InvoiceMappingDisplayNameColumn'],
                        ['column' => 'InvoiceMappingTradingCompanyColumn'],
                        ['column' => 'InvoiceMappingSiteColumn'],
                        ['column' => 'InvoiceMappingAssignedInvoiceColumn'],
                        ['column' => 'InvoiceMappingEmailContentColumn'],
                        ['column' => 'InvoiceMappingSendViaEmailColumn'],
                        ['column' => 'InvoiceMappingSendToFbaColumn'],
                    ],
                    'setVariable' => [
                        ['name' => 'settings', 'value' => 'InvoiceMappingDatatableSettings']
                    ],
                ]
            ],
            'InvoiceMappingDatatableSettings' => [
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
                    'width' => '300px',
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
            'InvoiceSendFromAddressColumn' => [
                'parameters' => [
                    'templateId' => 'sendFromAddress',
                    'viewModel' => 'InvoiceSendFromAddressColumnView',
                    'sortable' => false,
                    'hideable' => true,
                    'width' => '350px',
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
            'InvoiceSendFromAddressColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Send From Email'],
                    'template' => 'value.phtml',
                ],
            ],

            'InvoiceMappingChannelColumn' => [
                'parameters' => [
                    'templateId' => 'channel',
                    'viewModel' => 'InvoiceMappingChannelColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '100px',
                ],
            ],
            'InvoiceMappingDisplayNameColumn' => [
                'parameters' => [
                    'templateId' => 'displayName',
                    'viewModel' => 'InvoiceMappingDisplayNameColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '100px',
                ],
            ],
            'InvoiceMappingSiteColumn' => [
                'parameters' => [
                    'templateId' => 'site',
                    'viewModel' => 'InvoiceMappingSiteColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '50px',
                ],
            ],
            'InvoiceMappingTradingCompanyColumn' => [
                'parameters' => [
                    'templateId' => 'tradingCompany',
                    'viewModel' => 'InvoiceMappingTradingCompanyColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '100px',
                ],
            ],
            'InvoiceMappingAssignedInvoiceColumn' => [
                'parameters' => [
                    'templateId' => 'assignedInvoice',
                    'viewModel' => 'InvoiceMappingAssignedInvoiceColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '100px',
                ],
            ],
            'InvoiceMappingEmailContentColumn' => [
                'parameters' => [
                    'templateId' => 'emailContent',
                    'viewModel' => 'InvoiceMappingEmailContentColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '50px',
                ],
            ],
            'InvoiceMappingSendViaEmailColumn' => [
                'parameters' => [
                    'templateId' => 'sendViaEmail',
                    'viewModel' => 'InvoiceMappingSendViaEmailColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '50px',
                ],
            ],
            'InvoiceMappingSendToFbaColumn' => [
                'parameters' => [
                    'templateId' => 'sendToFba',
                    'viewModel' => 'InvoiceMappingSendToFbaColumnView',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '50px',
                ],
            ],
            'InvoiceMappingChannelColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Channel'],
                    'template' => 'value.phtml',
                ],
            ],
            'InvoiceMappingDisplayNameColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Display Name'],
                    'template' => 'value.phtml',
                ],
            ],
            'InvoiceMappingSiteColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Site'],
                    'template' => 'value.phtml',
                ],
            ],
            'InvoiceMappingTradingCompanyColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Trading Company'],
                    'template' => 'value.phtml',
                ],
            ],
            'InvoiceMappingAssignedInvoiceColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Assigned Invoice'],
                    'template' => 'value.phtml',
                ],
            ],
            'InvoiceMappingEmailContentColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Email Content'],
                    'template' => 'value.phtml',
                ],
            ],
            'InvoiceMappingSendViaEmailColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Email Invoice'],
                    'template' => 'value.phtml',
                ],
            ],
            'InvoiceMappingSendToFbaColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Send To FBA'],
                    'template' => 'value.phtml',
                ],
            ],

            'AccountEnableColumn' => [
                'parameters' => [
                    'templateId' => 'enable',
                    'viewModel' => 'AccountEnableColumnView',
                    'class' => 'toggle-col',
                    'sortable' => false,
                    'hideable' => false,
                    'width' => '100px',
                ],
            ],
            'AccountStatusColumn' => [
                'parameters' => [
                    'templateId' => 'status',
                    'viewModel' => 'AccountStatusColumnView',
                    'class' => 'status-col',
                    'sortable' => false,
                    'hideable' => false,
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
                    'repository' => ShippingMethodCacheStorage::class
                ]
            ],
            ShippingMethodCacheStorage::class => [
                'parameters'=> [
                    'storage' => ShippingMethodApiStorage::class,
                ],
            ],
            ShippingMethodApiStorage::class => [
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
                    'accountCreationService' => AmazonAccountCreationService::class,
                    'cryptor' => 'amazon_cryptor',
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
            ShipStationCreationService::class => [
                'parameters' => [
                    'channelAccount' => ShipStationAccount::class
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
            StockController::class => [
                'parameters' => [
                    'accountsTable' => 'StockSettingsAccountsTable', // defined in global.php
                ]
            ],
            ExportController::class => [
                'parameters' => [
                    'usageService' => 'order_count_usage_service'
                ]
            ],
            AccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'netdespatch_cryptor',
                    'mailer' => 'orderhub-mailer',
                    'viewModelFactory' => ViewModelFactory::class
                ]
            ],
            'ListingsCsvS3Adapter' => [
                'parameters' => [
                    'location' => ListingsCsvStorageS3::S3_BUCKET
                ]
            ],
            ListingsCsvStorageS3::class => [
                'parameters' => [
                    's3Adapter' => 'ListingsCsvS3Adapter'
                ]
            ]
        ]
    ]
];
