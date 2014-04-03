<?php
use CG\Account\Client\Storage\Api as AccountStorage;
use CG\Account\Client\Service as AccountService;
use CG\Ebay\Client\TradingApi;
use CG\Ebay\Account as EbayAccount;
use Guzzle\Http\Client as GuzzleHttpClient;
use Settings\Module;
use Settings\Controller\IndexController;
use Settings\Controller\ChannelController;
use Settings\Controller\EbayController;

return [
    'router' => [
        'routes' => [
            'Channel Management' => [
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
                    'Sales Channels' => [
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
                            'Sales Channel Create' => [
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
                'EbayGuzzle' => GuzzleHttpClient::class
            ],
            'EbayGuzzle' => [
                'parameters' => [
                    'baseUrl' => 'https://api.ebay.com/ws/api.dll'
                ]
            ],
            TradingApi::class => [
                'parameters' => [
                    'client' => 'EbayGuzzle',
                    'developerId' => '91dbbc3a-8765-4498-86ff-646f255323a8',
                    'applicationName' => 'WilkiLtd-beda-4d92-9c9f-7f7f9d283733',
                    'certificateId' => 'ba6edfbf-a5c5-48cd-a147-b9dbf0350fb3'
                ]
            ],
            AccountStorage::class => array(
                'parameters' => array(
                    'client' => 'account_guzzle'
                )
            ),
            AccountService::class => array(
                'parameters' => array(
                    'repository' => AccountStorage::class
                )
            ),
            EbayAccount::class => array(
                'parameters' => array(
                    'domain' => 'https://signin.ebay.com/ws/eBayISAPI.dll',
                    'ruName' => 'Wilki_Ltd-WilkiLtd-beda-4-kdighency',
                    'siteId' => 3
                )
            ),
            'preferences' => array(
                'CG\Stdlib\Log\LoggerInterface' => 'CG\Log\Logger'
            )
        ]
    ]
];