<?php
use CG\Account\Client\Storage\Api as AccountStorage;
use CG\Account\Client\Service as AccountService;
use CG\Amazon\Signer as AmazonSigner;
USE CG\Amazon\Account as AmazonAccount;
USE CG\Amazon\Account\Eu as AmazonAccountEu;
use CG\Ebay\Client\TradingApi;
use CG\Ebay\Account as EbayAccount;
use Guzzle\Http\Client as GuzzleHttpClient;
use Settings\Module;
use Settings\Controller\IndexController;
use Settings\Controller\ChannelController;
use Settings\Controller\EbayController;
use Settings\Controller\AmazonController;
use CG_UI\View\DataTable;
use Settings\Channel\Service;
use Zend\View\Model\ViewModel;

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
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => [
                                    'route' => '/amazon/:route',
                                    'defaults' => [
                                        'controller' => AmazonController::class,
                                        'action' => 'save'
                                    ]
                                ],
                                'may_terminate' => true
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
                                    ],
                                    'constraints' => [
                                        'account' => '[0-9]*'
                                    ],
                                ]
                            ]
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
            'aliases' => [
                'EbayGuzzle' => GuzzleHttpClient::class,
                'AccountList' => DataTable::class,
                'AccountListSettings' => DataTable\Settings::class,
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
                'AccountManageColumnView' => ViewModel::class
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
            'AccountEnableColumn' => [
                'parameters' => [
                    'viewModel' => 'AccountEnableColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountStatusColumn' => [
                'parameters' => [
                    'viewModel' => 'AccountStatusColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountChannelColumn' => [
                'parameters' => [
                    'viewModel' => 'AccountChannelColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountAccountColumn' => [
                'parameters' => [
                    'viewModel' => 'AccountAccountColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountTradingCompanyColumn' => [
                'parameters' => [
                    'viewModel' => 'AccountTradingCompanyColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountTokenStatusColumn' => [
                'parameters' => [
                    'viewModel' => 'AccountTokenStatusColumnView',
                    'sortable' => false,
                    'hideable' => false,
                ],
            ],
            'AccountManageColumn' => [
                'parameters' => [
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
            EbayAccount::class => [
                'parameters' => [
                    'domain' => 'https://signin.ebay.com/ws/eBayISAPI.dll',
                    'ruName' => 'Wilki_Ltd-WilkiLtd-beda-4-kdighency',
                    'siteId' => 3
                ]
            ],
            'preferences' => [
                'CG\Stdlib\Log\LoggerInterface' => 'CG\Log\Logger'
            ]
        ]
    ]
];