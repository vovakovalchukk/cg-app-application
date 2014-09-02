<?php
namespace Products;

use Products\Controller;
use Products\Controller\ProductsController;
use Zend\Mvc\Router\Http\Literal;
use Products\Controller\ProductsJsonController;
use CG\Product\Client\Service as ProductService;
use CG\Product\Storage\Api as ProductApiStorage;
use CG_UI\View\DataTable;
use CG\Stock\Service as StockService;
use CG\Stock\Storage\Api as StockApiStorage;
use CG\Stock\Location\Service as LocationService;
use CG\Stock\Location\Storage\Api as LocationApiStorage;
use CG\Listing\Service as ListingService;
use CG\Image\Service as ImageService;
use CG\Listing\Storage\Api as ListingApiStorage;
use CG\Image\Storage\Api as ImageApiStorage;
use Products\Controller\ListingsController;
use Products\Controller\ListingsJsonController;
use CG\Listing\Unimported\Service as UnimportedListingService;
use CG\Listing\Unimported\Storage\Api as UnimportedListingApiStorage;

return [
    'router' => [
        'routes' => [
            Module::ROUTE => [
                'type' => Literal::class,
                'options' => [
                    'route' => ProductsController::ROUTE_INDEX_URL,
                    'defaults' => [
                        'controller' => ProductsController::class,
                        'action' => 'index',
                        'breadcrumbs' => false,
                        'sidebar' => false
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    ProductsJsonController::ROUTE_AJAX => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/ajax',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'ajax'
                            ]
                        ],
                    ],
                    ProductsJsonController::ROUTE_STOCK_UPDATE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/stock/update',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'stockUpdate'
                            ]
                        ],
                    ],
                    ListingsController::ROUTE_INDEX => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => ListingsController::ROUTE_INDEX_URL,
                            'defaults' => [
                                'controller' => ListingsController::class,
                                'action' => 'index'
                            ]
                        ],
                        'child_routes' => [
                            ListingsJsonController::ROUTE_AJAX => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/ajax',
                                    'defaults' => [
                                        'controller' => ListingsJsonController::class,
                                        'action' => 'ajax'
                                    ]
                                ],
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                    ProductsJsonController::ROUTE_DELETE => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/delete',
                            'defaults' => [
                                'controller' => ProductsJsonController::class,
                                'action' => 'delete'
                            ]
                        ],
                    ],
                ]
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
            ProductService::class => [
                'parameters' => [
                    'repository' => ProductApiStorage::class,
                    'stockStorage' => StockService::class,
                    'listingStorage' => ListingService::class,
                    'imageStorage' => ImageService::class
                ]
            ],
            ProductApiStorage::class => [
               'parameters' => [
                   'client' => 'cg_app_guzzle'
               ]
            ],
            StockService::class => [
                'parameter' => [
                    'repository' => StockApiStorage::class,
                    'locationStorage' => LocationService::class
                ]
            ],
            ListingService::class => [
                'parameter' => [
                    'repository' => ListingApiStorage::class
                ]
            ],
            ImageService::class => [
                'parameter' => [
                    'repository' => ImageApiStorage::class
                ]
            ],
            UnimportedListingService::class => [
                'parameter' => [
                    'repository' => UnimportedListingApiStorage::class,
                    'imageStorage' => ImageService::class
                ]
            ],
            StockApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ListingApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ImageApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            LocationService::class => [
                'parameter' => [
                    'repository' => LocationApiStorage::class
                ]
            ],
            LocationApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            StockService::class => [
                'parameter' => [
                    'repository' => StockApiStorage::class,
                    'locationStorage' => LocationService::class
                ]
            ],
            StockApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
            LocationService::class => [
                'parameter' => [
                    'repository' => LocationApiStorage::class
                ]
            ],
            LocationApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
            UnimportedListingApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle',
                ]
            ],
        ],
    ],
    'navigation' => array(
        'application-navigation' => [
            'products' => [
                'label'  => 'Products',
                'route'  => 'Products',
                'sprite' => 'sprite-products-18-white',
                'order'  => 5,
                'pages'  => [
                    'importListings' => [
                        'id'    => 'importListings',
                        'label' => 'Import Listings',
                        'uri'   => implode(
                            '',
                            [
                                ProductsController::ROUTE_INDEX_URL,
                                ListingsController::ROUTE_INDEX_URL
                            ]
                        )
                    ]
                ]
            ]
        ]
    ),
];
