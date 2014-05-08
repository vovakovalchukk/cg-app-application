<?php
use CG\Account\Client\Storage\Api as AccountStorage;
use CG\Account\Client\Service as AccountService;
use CG\Amazon\Signer as AmazonSigner;
USE CG\Amazon\Account as AmazonAccount;
USE CG\Amazon\Account\Eu as AmazonAccountEu;
use CG\Ebay\Client\TradingApi;
use Guzzle\Http\Client as GuzzleHttpClient;
use Settings\Module;
use Settings\Controller\IndexController;
use Settings\Controller\ChannelController;
use Settings\Controller\EbayController;
use Settings\Controller\AmazonController;
use CG_UI\View\DataTable;
use Settings\Channel\Service;
use Zend\View\Model\ViewModel;
use CG\Account\Client\StorageInterface as AccountStorageInterface;
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\OrganisationUnit\StorageInterface as OUStorageInterface;
use CG\OrganisationUnit\Storage\Api as OUApiStorage;
use CG\Stdlib\Log\LoggerInterface;
use CG\Log\Logger;

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
                    ChannelController::ROUTE => [
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
            AmazonAccountEu::class => array(
                'parameters' => array(
                    'id' => '929b9241-7b26-4640-bc23-4e385329456b',
                    'awsAccessKeyId' => 'AKIAIDD3ZCDYV53OVQEA'
                )
            ),
            TradingApi::class => [
                'parameters' => [
                    'client' => 'EbayGuzzle',
                    'developerId' => '91dbbc3a-8765-4498-86ff-646f255323a8',
                    'applicationName' => 'WilkiLtd-beda-4d92-9c9f-7f7f9d283733',
                    'certificateId' => 'ba6edfbf-a5c5-48cd-a147-b9dbf0350fb3'
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
        ]
    ]
];