<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use CG\Cache\EventManagerInterface;
use CG\Zend\Stdlib\Cache\EventManager;
use CG\Zend\Stdlib\Db\Sql\Sql as CGSql;
use CG\Order\Shared\StorageInterface as OrderStorage;
use CG\Order\Client\StorageInterface as OrderClientStorage;
use CG\Order\Shared\Item\StorageInterface as ItemStorage;
use CG\Order\Shared\Tag\StorageInterface as OrderTagStorage;
use CG\Order\Shared\Batch\StorageInterface as OrderBatchStorage;
use CG\OrganisationUnit\StorageInterface as OrganisationUnitStorage;
use CG\Order\Client\Storage\Api as OrderApiClient;
use CG\Order\Client\Item\Storage\Api as ItemApiClient;
use CG\Order\Client\Tag\Storage\Api as OrderTagApiClient;
use CG\Order\Client\Batch\Storage\Api as OrderBatchApiClient;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitClient;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\UserPreference\Client\Storage\Api as UserPreferenceStorage;
use Zend\Session\ManagerInterface as SessionManagerInterface;
use Zend\Session\SessionManager;
use Orders\Order\Batch\Service as OrderBatchService;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use CG_UI\View\DataTable;
use Zend\View\Model\ViewModel;
use CG\Channel\AccountDisabler;

use CG\Location\Service as LocationService;
use CG\Location\Storage\Api as LocationApi;
use CG\Location\Mapper as LocationMapper;

use CG\Stock\Location\Storage\Api as LocationApiStorage;
use CG\Stock\Location\StorageInterface as LocationStorageInterface;

use CG\Billing\Transaction\StorageInterface as TransactionStorage;
use CG\Billing\Transaction\Storage\Api as TransactionApiStorage;
use CG\Billing\BillingWindow\Storage\Api as BillingWindowStorage;
use CG\Billing\BillingWindow\Service as BillingWindowService;

use CG_UI\Module as UI;
use CG_Permission\Service as PermissionService;
use CG\Stock\Audit\Storage\Queue as StockAuditQueue;

// Logging
use CG\Log\Shared\Storage\Redis\Channel as RedisChannel;
use CG\Log\Psr\Logger as CGPsrLogger;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitStorageApi;

//Order Counts
use CG\Order\Shared\OrderCounts\Storage\Api as OrderCountsApi;
// Order usage
use CG_Usage\Service as UsageService;

// Settings
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Client\Storage\Api as InvoiceSettingsApiStorage;
use CG\Settings\Product\StorageInterface as ProductSettingsStorage;
use CG\Settings\Product\Storage\Api as ProductSettingsStorageApi;

// Discount
use CG\Billing\Discount\StorageInterface as DiscountStorage;
use CG\Billing\Discount\Storage\Api as DiscountApiStorage;

// SubscriptionDiscount
use CG\Billing\SubscriptionDiscount\StorageInterface as SubscriptionDiscountStorage;
use CG\Billing\SubscriptionDiscount\Storage\Api as SubscriptionDiscountApiStorage;

use CG\Account\Cleanup\Service as AccountCleanupService;
use CG\Listing\Service\Service as ListingService;
use CG\Listing\Unimported\Service as UnimportedListingService;
use CG\Listing\Mapper as ListingMapper;
use CG\Listing\Unimported\Mapper as UnimportedListingMapper;
use CG\Listing\Storage\Api as ListingApi;
use CG\Listing\Unimported\Storage\Api as UnimportedListingApi;

// Tax
use CG\Ekm\Product\TaxRate\Mapper as EkmTaxRateMapper;
use CG\Ekm\Product\TaxRate\Storage\Cache as EkmTaxRateCache;
use CG\Ekm\Product\TaxRate\Storage\Db as EkmTaxRateDb;
use CG\Ekm\Product\TaxRate\Repository as EkmTaxRateRepository;
use CG\Ekm\Product\TaxRate\Service as EkmTaxRateService;

// Stock Import
use CG\Stock\Import\File\Storage\Db as StockImportFileDb;
use CG\Stock\Import\File\Mapper as StockImportFileMapper;

// Communication
use CG\Communication\Headline\StorageInterface as HeadlineStorage;
use CG\Communication\Headline\Storage\Api as HeadlineApi;
use CG\Communication\Message\StorageInterface as MessageStorage;
use CG\Communication\Message\Storage\Api as MessageApi;
use CG\Communication\Thread\StorageInterface as ThreadStorage;
use CG\Communication\Thread\Storage\Api as ThreadApi;

// Amazon\Thread\Additional
use CG\Amazon\Thread\Additional\Mapper as AmzThreadAdditionalMapper;
use CG\Amazon\Thread\Additional\Storage\Cache as AmzThreadAdditionalCache;
use CG\Amazon\Thread\Additional\Storage\Db as AmzThreadAdditionalDb;
use CG\Amazon\Thread\Additional\StorageInterface as AmzThreadAdditionalStorage;
use CG\Amazon\Thread\Additional\Repository as AmzThreadAdditionalRepository;

// ApiCredentials
use CG\ApiCredentials\StorageInterface as ApiCredentialsStorage;
use CG\ApiCredentials\Storage\Api as ApiCredentialsApi;

// Invoice Image Client
use CG\Template\Image\ClientInterface as ImageTemplateClient;
use CG\Template\Image\Client\Guzzle as ImageTemplateGuzzleClient;

// Shipping options
use CG\Channel\CarrierBookingOptionsInterface as ChannelCarrierBookingOptionsInterface;
use CG\Channel\CarrierBookingOptionsRepository as ChannelCarrierBookingOptionsRepository;
use CG\Channel\ShippingChannelsProviderInterface as ChannelShippingChannelsProviderInterface;
use CG\Channel\ShippingChannelsProviderRepository as ChannelShippingChannelsProviderRepository;
use CG\Channel\ShippingOptionsProviderInterface as ChannelShippingOptionsProviderInterface;
use CG\Channel\ShippingOptionsProviderRepository as ChannelShippingOptionsProviderRepository;
use CG\Channel\CarrierProviderServiceInterface as ChannelCarrierProviderServiceInterface;
use CG\Channel\CarrierProviderServiceRepository as ChannelCarrierProviderServiceRepository;

// Dataplug
use CG\Dataplug\Carrier\Service as DataplugCarrierService;
use CG\Order\Shared\Label\StorageInterface as OrderLabelStorage;
use CG\Order\Client\Label\Storage\Api as OrderLabelApiStorage;
use CG\Product\Detail\StorageInterface as ProductDetailStorage;
use CG\Product\Detail\Storage\Api as ProductDetailApiStorage;
use CG\Dataplug\Account\Service as DataplugAccountService;
use CG\Dataplug\Account\Storage\Api as DataplugAccountApi;
use CG\Dataplug\Request\Factory\CreateCarrier as DataplugCreateCarrierRequestFactory;
use CG\Dataplug\Request\Factory\UpdateCarrier as  DataplugUpdateCarrierRequestFactory;
use CG\Dataplug\Carriers as DataplugCarriers;
use CG\Account\Shared\Manifest\StorageInterface as AccountManifestStorage;
use CG\Account\Client\Manifest\Storage\Api as AccountManifestApiStorage;
use CG\Dataplug\Request\Carrier as DataplugCarrier;
use CG\Dataplug\Order\Service as DataplugOrderService;

// NetDespatch
use CG\NetDespatch\ShippingOptionsProvider as NetDespatchShippingOptionsProvider;
use CG\NetDespatch\Order\Service as NetDespatchOrderService;
use CG\NetDespatch\ShippingService as NetDespatchShippingService;
use CG\NetDespatch\Order\CreateService as NetDespatchOrderCreateService;
use CG\NetDespatch\Account\CreationService as NetDespatchAccountCreationService;

// Transactions
use CG\Transaction\ClientInterface as TransactionClient;
use CG\Transaction\Client\Redis as RedisTransactionClient;

//StockLogs
use CG\Stock\Audit\Combined\StorageInterface as StockLogStorage;
use CG\Stock\Audit\Combined\Storage\Api as StockLogApiStorage;

// Customer Order Counts
use CG\Order\Shared\CustomerCounts\StorageInterface as CustomerCountStorage;
use CG\Order\Shared\CustomerCounts\Repository as CustomerCountRepository;
use CG\Order\Shared\CustomerCounts\Storage\Cache as CustomerCountCacheStorage;
use CG\Order\Shared\CustomerCounts\Storage\OrderLookup as CustomerCountOrderLookupStorage;

// Locking
use CG\Locking\StorageInterface as LockingStorage;
use CG\Redis\Locking\Storage as LockingRedisStorage;

// Amazon Logistics
use CG\Amazon\Carrier\Service as AmazonCarrierService;
use CG\Amazon\Carrier\ShippingChannelsProvider as AmazonShippingChannelsProvider;
use CG\Amazon\Carrier\CarrierProviderService as AmazonCarrierProvider;
use CG\Amazon\ShippingService\Service as AmazonShippingServiceService;
use CG\Amazon\ShippingService\StorageInterface as AmazonShippingServiceStorage;
use CG\Amazon\ShippingService\Storage\Api as AmazonShippingServiceApiStorage;

// Accounts
use CG\Account\Client\StorageInterface as AccountStorage;
use CG\Account\Client\Storage\Api as AccountApiStorage;

// CourierAdapters
use CG\CourierAdapter\Provider\Implementation\CarrierBookingOptions as CourierAdapterProviderCarrierBookingOptions;
use CG\CourierAdapter\Provider\Implementation\Service as CourierAdapterProviderImplementationService;
use CG\CourierAdapter\Provider\Label\Service as CourierAdapterProviderLabelService;

// Amazon MCF (Multi-Channel Fulfilment)
use CG\Amazon\Mcf\ShippingChannelsProvider as AmazonMcfShippingChannelsProvider;
use CG\Amazon\Mcf\CarrierBookingOptions as AmazonMcfCarrierBookingOptions;
use CG\Amazon\Mcf\CarrierProviderService as AmazonMcfCarrierProviderService;

$config = array(
    'di' => array(
        'definition' => [
            'class' => [
                ChannelShippingChannelsProviderRepository::class => [
                    'methods' => [
                        'addProvider' => [
                            'provider' => [
                                'type' => ChannelShippingChannelsProviderInterface::class,
                                'required' => true
                            ]
                        ]
                    ]
                ],
                ChannelShippingOptionsProviderRepository::class => [
                    'methods' => [
                        'addProvider' => [
                            'provider' => [
                                'type' => ChannelShippingOptionsProviderInterface::class,
                                'required' => true
                            ]
                        ]
                    ]
                ],
                ChannelCarrierBookingOptionsRepository::class => [
                    'methods' => [
                        'addProvider' => [
                            'provider' => [
                                'type' => ChannelCarrierBookingOptionsInterface::class,
                                'required' => true
                            ]
                        ]
                    ]
                ],
                ChannelCarrierProviderServiceRepository::class => [
                    'methods' => [
                        'addProvider' => [
                            'provider' => [
                                'type' => ChannelCarrierProviderServiceInterface::class,
                                'required' => true
                            ]
                        ]
                    ]
                ],
            ]
        ],
        'instance' => array(
            'preferences' => array(
                EventManagerInterface::class => EventManager::class,
                OrderStorage::class => OrderApiClient::class,
                ItemStorage::class => ItemApiClient::class,
                OrderClientStorage::class => OrderApiClient::class,
                OrderTagStorage::class => OrderTagApiClient::class,
                OrderBatchStorage::class => OrderBatchApiClient::class,
                OrganisationUnitStorage::class => OrganisationUnitClient::class,
                SessionManagerInterface::class => SessionManager::class,
                ServiceLocatorInterface::class => ServiceManager::class,
                LocationStorageInterface::class => LocationApiStorage::class,
                TransactionStorage::class => TransactionApiStorage::class,
                DiscountStorage::class => DiscountApiStorage::class,
                SubscriptionDiscountStorage::class => SubscriptionDiscountApiStorage::class,
                ThreadStorage::class => ThreadApi::class,
                MessageStorage::class => MessageApi::class,
                HeadlineStorage::class => HeadlineApi::class,
                AmzThreadAdditionalStorage::class => AmzThreadAdditionalRepository::class,
                ApiCredentialsStorage::class => ApiCredentialsApi::class,
                ImageTemplateClient::class => ImageTemplateGuzzleClient::class,
                ProductSettingsStorage::class => ProductSettingsStorageApi::class,
                OrderLabelStorage::class => OrderLabelApiStorage::class,
                ProductDetailStorage::class => ProductDetailApiStorage::class,
                AccountManifestStorage::class => AccountManifestApiStorage::class,
                TransactionClient::class => RedisTransactionClient::class,
                StockLogStorage::class => StockLogApiStorage::class,
                UsageService::class => 'order_count_usage_service',
                CustomerCountStorage::class => CustomerCountRepository::class,
                LockingStorage::class => LockingRedisStorage::class,
                AmazonShippingServiceStorage::class => AmazonShippingServiceApiStorage::class,
                AccountStorage::class => AccountApiStorage::class,
                PsrLoggerInterface::class => CGPsrLogger::class,
            ),
            'aliases' => [
                'amazonWriteCGSql' => CGSql::class,
                'StockSettingsAccountsTable' => DataTable::class,
                'StockSettingsAccountsTableSettings' => DataTable\Settings::class,
                'StockSettingsAccountsChannelColumn' => DataTable\Column::class,
                'StockSettingsAccountsAccountColumn' => DataTable\Column::class,
                'StockSettingsAccountsMaxColumn' => DataTable\Column::class,
                'StockSettingsAccountsFixedColumn' => DataTable\Column::class,
                'StockSettingsAccountsChannelColumnView' => ViewModel::class,
                'StockSettingsAccountsAccountColumnView' => ViewModel::class,
                'StockSettingsAccountsMaxColumnView' => ViewModel::class,
                'StockSettingsAccountsFixedColumnView' => ViewModel::class,
            ],
            'amazonWriteCGSql' => [
                'parameter' => [
                    'adapter' => 'amazonWrite'
                ]
            ],
            AccountCleanupService::class => [
                'listingService' => ListingService::class,
                'unimportedListingService' => UnimportedListingService::class
            ],
            OrderApiClient::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ItemApiClient::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            OrderBatchService::class => [
                'parameters' => [
                    'redisClient' => 'reliable_redis'
                ]
            ],
            OrderBatchApiClient::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            OrderTagApiClient::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            OrderBatchApiClient::class => [
                'parameter' => array(
                    'client' => 'cg_app_guzzle'
                )
            ],
            OrganisationUnitClient::class => [
                'parameter' => [
                    'client' => 'directory_guzzle'
                ]
            ],
            OrderCountsApi::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            UserPreferenceStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            InvoiceSettingsApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            BillingWindowStorage::class => [
                'parameter' => [
                    'client' => 'billing_guzzle'
                ]
            ],
            BillingWindowService::class => [
                'parameter' => [
                    'repository' => BillingWindowStorage::class
                ]
            ],
            UserPreferenceService::class => [
                'parameter' => [
                    'repository' => UserPreferenceStorage::class
                ]
            ],
            InvoiceSettingsService::class => array(
                'parameters' => array(
                    'repository' => InvoiceSettingsApiStorage::class
                )
            ),
            LocationService::class => [
                'parameters' => [
                    'repository' => LocationApi::class,
                    'mapper' => LocationMapper::class
                ]
            ],
            LocationApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            LocationApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            TransactionApiStorage::class => [
                'parameter' => [
                    'client' => 'billing_guzzle',
                ],
            ],
            StockAuditQueue::class => [
                'parameters' => [
                    'client' => 'reliable_redis'
                ]
            ],
            RedisChannel::class => [
                'parameters' => [
                    'rootOrganisationUnitProvider' => OrganisationUnitService::class
                ]
            ],
            OrganisationUnitService::class => [
                'parameters' => [
                    'repository' => OrganisationUnitStorageApi::class,
                ]
            ],
            OrganisationUnitStorageApi::class => [
                'parameters' => [
                    'client' => 'directory_guzzle',
                ]
            ],
            DiscountApiStorage::class => [
                'parameters' => [
                    'client' => 'billing_guzzle'
                ]
            ],
            SubscriptionDiscountApiStorage::class => [
                'parameters' => [
                    'client' => 'billing_guzzle'
                ]
            ],
            ListingService::class => [
                'parameters' => [
                    'repository' => ListingApi::class,
                    'mapper' => ListingMapper::class
                ]
            ],
            ListingApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            UnimportedListingService::class => [
                'parameters' => [
                    'repository' => UnimportedListingApi::class,
                    'mapper' => UnimportedListingMapper::class
                ]
            ],
            UnimportedListingApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            EkmTaxRateCache::class => [
                'parameter' => [
                    'mapper' => EkmTaxRateMapper::class
                ]
            ],
            EkmTaxRateDb::class => array(
                'parameters' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => EkmTaxRateMapper::class
                )
            ),
            EkmTaxRateRepository::class => [
                'parameters' => [
                    'storage' => EkmTaxRateCache::class,
                    'repository' => EkmTaxRateDb::class,
                ]
            ],
            EkmTaxRateService::class => [
                'parameters' => [
                    'cryptor' => 'ekm_cryptor',
                    'repository' => EkmTaxRateRepository::class
                ]
            ],
            StockImportFileDb::class => [
                'parameter' => [
                    'readSql' => 'appReadSql',
                    'fastReadSql' => 'appFastReadSql',
                    'writeSql' => 'appWriteSql',
                    'mapper' => StockImportFileMapper::class
                ]
            ],
            ThreadApi::class => [
                'parameters' => [
                    'client' => 'communication_guzzle'
                ]
            ],
            MessageApi::class => [
                'parameters' => [
                    'client' => 'communication_guzzle'
                ]
            ],
            HeadlineApi::class => [
                'parameters' => [
                    'client' => 'communication_guzzle'
                ]
            ],
            AmzThreadAdditionalDb::class => [
                'parameters' => [
                    'readSql' => 'amazonReadSql',
                    'fastReadSql' => 'amazonFastReadSql',
                    'writeSql' => 'amazonWriteSql',
                    'mapper' => AmzThreadAdditionalMapper::class,
                ]
            ],
            AmzThreadAdditionalCache::class => [
                'parameters' => [
                    'mapper' => AmzThreadAdditionalMapper::class
                ]
            ],
            AmzThreadAdditionalRepository::class => [
                'parameters' => [
                    'storage' => AmzThreadAdditionalCache::class,
                    'repository' => AmzThreadAdditionalDb::class
                ]
            ],
            ApiCredentialsApi::class => [
                'parameters' => [
                    'client' => 'directory_guzzle'
                ]
            ],
            ProductSettingsStorageApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            StockLogApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],

            'StockSettingsAccountsTable' => [
                'parameters' => [
                    'variables' => [
                        'id' => 'accounts-table',
                        'sortable' => 'false',
                        'class' => 'fixed-header fixed-footer',
                        'width' => '100%',
                    ],
                ],
                'injections' => [
                    'addColumn' => [
                        ['column' => 'StockSettingsAccountsChannelColumn'],
                        ['column' => 'StockSettingsAccountsAccountColumn'],
                        ['column' => 'StockSettingsAccountsMaxColumn'],
                        ['column' => 'StockSettingsAccountsFixedColumn'],
                    ],
                    'setVariable' => [
                        ['name' => 'settings', 'value' => 'StockSettingsAccountsTableSettings']
                    ],
                ]
            ],
            'StockSettingsAccountsTableSettings' => [
                'parameters' => [
                    'scrollHeightAuto' => true,
                    'footer' => false,
                ]
            ],
            'StockSettingsAccountsChannelColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Channel'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockSettingsAccountsChannelColumn' => [
                'parameters' => [
                    'column' => 'channel',
                    'viewModel' => 'StockSettingsAccountsChannelColumnView',
                    'class' => 'channel-col',
                    'sortable' => false,
                ],
            ],
            'StockSettingsAccountsAccountColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Account'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockSettingsAccountsAccountColumn' => [
                'parameters' => [
                    'column' => 'account',
                    'viewModel' => 'StockSettingsAccountsAccountColumnView',
                    'class' => 'account-col',
                    'sortable' => false,
                ],
            ],
            'StockSettingsAccountsMaxColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'List up to a maximum of'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockSettingsAccountsMaxColumn' => [
                'parameters' => [
                    'column' => 'max',
                    'viewModel' => 'StockSettingsAccountsMaxColumnView',
                    'class' => 'max-col',
                    'sortable' => false,
                    'width' => '100px',
                ],
            ],
            'StockSettingsAccountsFixedColumnView' => [
                'parameters' => [
                    'variables' => ['value' => 'Fix the level at'],
                    'template' => 'value.phtml',
                ],
            ],
            'StockSettingsAccountsFixedColumn' => [
                'parameters' => [
                    'column' => 'fixed',
                    'viewModel' => 'StockSettingsAccountsFixedColumnView',
                    'class' => 'fixed-col',
                    'sortable' => false,
                    'width' => '100px',
                ],
            ],
            AccountDisabler::class => [
                'parameters' => [
                    'predisClient' => 'unreliable_redis'
                ]
            ],
            OrderLabelApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            ProductDetailApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            DataplugCreateCarrierRequestFactory::class => [
                'parameters' => [
                    'cryptor' => 'dataplug_cryptor'
                ]
            ],
            DataplugUpdateCarrierRequestFactory::class => [
                'parameters' => [
                    'cryptor' => 'dataplug_cryptor'
                ]
            ],
            DataplugAccountService::class => [
                'parameters' => [
                    'repository' => DataplugAccountApi::class
                ]
            ],
            DataplugAccountApi::class => [
                'parameters' => [
                    'client' => 'account_guzzle'
                ]
            ],
            AccountManifestApiStorage::class => [
                'parameters' => [
                    'client' => 'account_guzzle'
                ]
            ],
            AccountApiStorage::class => [
                'parameters' => [
                    'client' => 'account_guzzle'
                ]
            ],
            ChannelShippingChannelsProviderRepository::class => [
                'injections' => [
                    'addProvider' => [
                        ['provider' => DataplugCarriers::class],
                        ['provider' => NetDespatchShippingOptionsProvider::class],
                        // Amazon MCF must come before Amazon Logistics
                        ['provider' => AmazonMcfShippingChannelsProvider::class],
                        ['provider' => AmazonShippingChannelsProvider::class],
                        ['provider' => CourierAdapterProviderImplementationService::class],
                    ]
                ]
            ],
            ChannelShippingOptionsProviderRepository::class => [
                'injections' => [
                    'addProvider' => [
                        ['provider' => DataplugCarrierService::class],
                        ['provider' => NetDespatchShippingOptionsProvider::class],
                        ['provider' => CourierAdapterProviderImplementationService::class],
                    ]
                ]
            ],
            ChannelCarrierBookingOptionsRepository::class => [
                'injections' => [
                    'addProvider' => [
                        ['provider' => DataplugCarrierService::class],
                        ['provider' => NetDespatchShippingOptionsProvider::class],
                        // Amazon MCF must come before Amazon Logistics
                        ['provider' => AmazonMcfCarrierBookingOptions::class],
                        ['provider' => AmazonShippingChannelsProvider::class],
                        ['provider' => CourierAdapterProviderCarrierBookingOptions::class],
                    ]
                ]
            ],
            ChannelCarrierProviderServiceRepository::class => [
                'injections' => [
                    'addProvider' => [
                        ['provider' => DataplugOrderService::class],
                        ['provider' => NetDespatchOrderService::class],
                        // Amazon MCF must come before Amazon Logistics
                        ['provider' => AmazonMcfCarrierProviderService::class],
                        ['provider' => AmazonCarrierProvider::class],
                        ['provider' => CourierAdapterProviderLabelService::class],
                    ]
                ]
            ],
            DataplugCarrierService::class => [
                'parameters' => [
                    'carriersConfig' => [
                        [
                            'channelName' => DataplugCarriers::DHL,
                            'displayName' => 'DHL',
                            'code' => DataplugCarrier\Dhl::CODE,
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Domestic Account no'],
                                ['name' => 'International Account no'],
                                ['name' => 'Password', 'inputType' => 'password'],
                                ['name' => 'Site ID']
                            ],
                            'services' => [
                                ['value' => 'DHLEXPWW00PA', 'name' => 'Express Worldwide Parcel'],
                                ['value' => 'DHLEXPWW00DC', 'name' => 'Express Worldwide Docs'],
                                ['value' => 'DHLECONSELPA', 'name' => 'Economy Select Parcel'],
                                ['value' => 'DHLECONSELDC', 'name' => 'Economy Select Docs'],
                                ['value' => 'DHLEXP0000PA0900', 'name' => 'Express 09:00am Parcel'],
                                ['value' => 'DHLEXP0000DC0900', 'name' => 'Express 09:00am Docs'],
                                ['value' => 'DHLEXP0000PA1200', 'name' => 'Express 12:00noon Parcel'],
                                ['value' => 'DHLEXP0000DC1200', 'name' => 'Express 12:00noon Docs'],
                                ['value' => 'DHLDOMEXP0PA', 'name' => 'Domestic Express Parcel'],
                                ['value' => 'DHLDOMEXP0PA0900', 'name' => 'Domestic Express 09:00am Parcel'],
                                ['value' => 'DHLDOMEXP0PA1200', 'name' => 'Domestic Express 12:00noon Parcel']
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                                'deliveryInstructions' => false,
                            ],
                            'links' => [
                                [
                                    'description' => 'Follow this guide to get your credentials',
                                    'source' => 'http://help.orderhub.io/hc/en-us/articles/206517099-DHL',
                                ],
                            ],
                        ],
                        /*
                        [
                            'channelName' => DataplugCarriers::DPD,
                            'displayName' => 'DPD',
                            'code' => DataplugCarrier\Dpd::CODE,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'User ID'],
                                ['name' => 'SLID'],
                                ['name' => 'Authorisation Code'],
                                ['name' => 'Start Sequence', 'label' => 'Start Parcel Sequence'],
                                ['name' => 'End Sequence', 'label' => 'End Parcel Sequence'],
                            ],
                            'services' => [
                                ['value' => 'DPDEXPR000PA', 'name' => 'Express Parcel'],
                                ['value' => 'DPDEXPDOC0PA', 'name' => 'Express Document'],
                                ['value' => 'DPDEXP00EUPA', 'name' => 'Express EU Parcel'],
                                ['value' => 'DPDTWODAY0PA', 'name' => 'Two Day Parcel'],
                                ['value' => 'DPDNEXTDAYPA', 'name' => 'Next Day Parcel'],
                                ['value' => 'DPDNEXTDAYPA1200', 'name' => 'Next Day 12:00noon Parcel'],
                                ['value' => 'DPDNEXTDAYPA1030', 'name' => 'Next Day 10:30am Parcel'],
                                ['value' => 'DPDSAT0000PA', 'name' => 'Saturday Parcel'],
                                ['value' => 'DPDSAT0000PA1200', 'name' => 'Saturday 12:00noon Parcel'],
                                ['value' => 'DPDSAT0000PA1030', 'name' => 'Saturday 10:30am Parcel'],
                                ['value' => 'DPDCLASSICPA', 'name' => 'Classic Parcel'],
                                ['value' => 'DPDNEXTDAYPK', 'name' => 'Next day Express Pack'],
                                ['value' => 'DPDNEXTDAYPK1200', 'name' => 'Next day 12:00noon Express Pack'],
                                ['value' => 'DPDNEXTDAYPK1030', 'name' => 'Next day 10:30am Express Pack'],
                                ['value' => 'DPDTWODAY0PL', 'name' => 'Two Day Pallet'],
                                ['value' => 'DPDNEXTDAYPL', 'name' => 'Next Day Pallet'],
                                ['value' => 'DPDNEXTDAYPL1200', 'name' => 'Next Day 12:00noon Pallet'],
                                ['value' => 'DPDNEXTDAYPL1030', 'name' => 'Next Day 10:30am Pallet'],
                                ['value' => 'DPDSAT0000PL', 'name' => 'Saturday Pallet'],
                                ['value' => 'DPDSAT0000PL1200', 'name' => 'Saturday 12:00noon Pallet'],
                                ['value' => 'DPDSAT0000PL1030', 'name' => 'Saturday 10:30am Pallet'],
                                ['value' => 'DPDCLASAIRPA', 'name' => 'Classic Air Parcel'],
                                ['value' => 'DPDSAT0000PK', 'name' => 'Saturday Express Pack'],
                                ['value' => 'DPDSAT0000PK1030', 'name' => 'Saturday 10:30am Express Pack'],
                                ['value' => 'DPDSAT0000PK1200', 'name' => 'Saturday 12:00noon Express Pack']
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                            'links' => [
                                [
                                    'description' => 'Follow this guide to get your credentials',
                                    'source' => 'http://help.orderhub.io/hc/en-us/articles/206517189-DPD',
                                ],
                            ],
                        ],
                        */
                        [
                            'channelName' => DataplugCarriers::FEDEX,
                            'displayName' => 'FedEx',
                            'code' => DataplugCarrier\Fedex::CODE,
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Key'],
                                ['name' => 'Password', 'inputType' => 'password'],
                                ['name' => 'Trans ID', 'inputType' => 'hidden', 'value' => 'ORDERHUB'],
                                ['name' => 'Meter No'],
                            ],
                            'services' => [
                                ['value' => 'FDXINTPRIOPA', 'name' => 'International Priority Parcel'],
                                ['value' => 'FDXINTECONPA', 'name' => 'International Economy Parcel'],
                                ['value' => 'FDXINTPRIOLT', 'name' => 'International Priority Letter'],
                                ['value' => 'FDXINTPRIOPK', 'name' => 'International Priority Pak'],
                                ['value' => 'FDXINTECONPK', 'name' => 'International Economy Pak'],
                                ['value' => 'FDXINTPRIO10', 'name' => 'International Priority 10KG Box'],
                                ['value' => 'FDXINTPRIO25', 'name' => 'International Priority 25KG Box'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                                'deliveryInstructions' => false,
                            ],
                            'links' => [
                                [
                                    'description' => 'Follow this guide to get your credentials',
                                    'source' => 'http://help.orderhub.io/hc/en-us/articles/207217195-Fedex',
                                ],
                            ],
                        ],
                        /*
                        [
                            'channelName' => DataplugCarriers::INTERLINK,
                            'displayName' => 'Interlink',
                            'code' => DataplugCarrier\Interlink::CODE,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'FTP Username', 'required' => false],
                                ['name' => 'FTP Password', 'required' => false],
                                ['name' => 'Authorisation Code'],
                                ['name' => 'Start Sequence', 'label' => 'Start Parcel Sequence'],
                                ['name' => 'End Sequence', 'label' => 'End Parcel Sequence'],
                                ['name' => 'Order Start Sequence', 'label' => 'Start Consignment Sequence'],
                                ['name' => 'Order End Sequence', 'label' => 'End Consignment Sequence'],
                            ],
                            'services' => [
                                ['value' => 'INTHOMECALPK1', 'name' => 'HomeCall Express Pack 1 Parcel'],
                                ['value' => 'INTNEXTDAYPK1', 'name' => 'Next Day Express Pack 1 Parcel'],
                                ['value' => 'INTNEXTDAYPK11030', 'name' => 'By 10:30am Express Pack 1 Parcel'],
                                ['value' => 'INTNEXTDAYPK11200', 'name' => 'By 12:00am Express Pack 1 Parcel'],
                                ['value' => 'INTSAT000PK1', 'name' => 'Saturday Express Pack 1 Parcel'],
                                ['value' => 'INTSATUDAYPK11030', 'name' => 'Saturday by 10:30am Express Pack 1 Parcel'],
                                ['value' => 'INTSATUDAYPK11200', 'name' => 'Saturday by 12:00am Express Pack 1 Parcel'],
                                ['value' => 'INTSUN000PK1', 'name' => 'Sunday Express Pack 1 Parcel'],
                                ['value' => 'INTSUNDAYPK11030', 'name' => 'Sunday by 10:30am Express Pack 1 Parcel'],
                                ['value' => 'INTSUNDAYPK11200', 'name' => 'Sunday by 12:00am Express Pack 1 Parcel'],
                                ['value' => 'INTHOMECALPK5', 'name' => 'HomeCall Express Pack 5 Parcel'],
                                ['value' => 'INTNEXTDAYPK5', 'name' => 'Next Day Express Pack 5 Parcel'],
                                ['value' => 'INTNEXTDAYPK51030', 'name' => 'By 10:30am Express Pack 5 Parcel'],
                                ['value' => 'INTNEXTDAYPK51200', 'name' => 'By 12:00am Express Pack 5 Parcel'],
                                ['value' => 'INTSAT000PK5', 'name' => 'Saturday Express Pack 5 Parcel'],
                                ['value' => 'INTSATUDAYPK51030', 'name' => 'Saturday by 10:30am Express Pack 5 Parcel'],
                                ['value' => 'INTSATUDAYPK51200', 'name' => 'Saturday by 12:00am Express Pack 5 Parcel'],
                                ['value' => 'INTSUN000PK5', 'name' => 'Sunday Express Pack 5 Parcel'],
                                ['value' => 'INTSUNDAYK5A1030', 'name' => 'Sunday by 10:30am Express Pack 5 Parcel'],
                                ['value' => 'INTSUNDAYPK51200', 'name' => 'Sunday by 12:00am Express Pack 5 Parcel'],
                                ['value' => 'INTHOMECALPA', 'name' => 'HomeCall Parcel'],
                                ['value' => 'INTNEXTDAYPA', 'name' => 'Next Day Parcel'],
                                ['value' => 'INTNEXTDAYPA1030', 'name' => 'By 10:30am Parcel'],
                                ['value' => 'INTNEXTDAYPA1200', 'name' => 'By 12:00am Parcel'],
                                ['value' => 'INTSAT0000PA', 'name' => 'Saturday Parcel'],
                                ['value' => 'INTSATUDAYPA1030', 'name' => 'Saturday by 10:30am Parcel'],
                                ['value' => 'INTSATUDAYPA1200', 'name' => 'Saturday by 12:00am Parcel'],
                                ['value' => 'INTSUN0000PA', 'name' => 'Sunday Parcel'],
                                ['value' => 'INTSUNDAYPA1030', 'name' => 'Sunday by 10:30am Parcel'],
                                ['value' => 'INTSUNDAYPA1200', 'name' => 'Sunday by 12:00am Parcel'],
                                ['value' => 'INTTWODAY0PA', 'name' => 'Two Day Parcel'],
                                ['value' => 'DPDEXPAIR0PA', 'name' => 'Air Express Parcel'],
                                ['value' => 'DPDCLASSICPA', 'name' => 'Classic Parcel'],
                                ['value' => 'DPDCLASAIRPA', 'name' => 'Classic Air Parcel'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                            'links' => [
                                [
                                    'description' => 'Follow this guide to get your credentials',
                                    'source' => 'http://help.orderhub.io/hc/en-us/articles/206521369-Interlink-Express',
                                ],
                            ],
                        ],
                        */
                        [
                            'channelName' => DataplugCarriers::MYHERMES,
                            'displayName' => 'MyHermes',
                            'salesChannelName' => 'Hermes',
                            'code' => DataplugCarrier\Myhermes::CODE,
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Email'],
                                ['name' => 'Password', 'inputType' => 'password'],
                                ['name' => 'Client ID'],
                                ['name' => 'Client Secret'],
                            ],
                            'services' => [
                                ['value' => 'MYHER48HRPAKG', 'name' => '48 Hour Parcel'],
                                ['value' => 'MYHER48HRSFPAKG', 'name' => '48 Hour Signed For Parcel'],
                            ],
                            'options' => [
                                'height' => false,
                                'width' => false,
                                'length' => false,
                                'insurance' => false,
                                'signature' => false,
                            ],
                        ],
                        /*
                        [
                            'channelName' => DataplugCarriers::PARCELFORCE,
                            'displayName' => 'Parcelforce',
                            'code' => DataplugCarrier\Parcelforce::CODE,
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Contract no'],
                                ['name' => 'User ID'],
                                ['name' => 'Password', 'inputType' => 'password'],
                            ],
                            'services' => [
                                ['value' => 'PFWEXP0024PA0900', 'name' => 'Express 9 Parcel'],
                                ['value' => 'PFWEXP0024PA1000', 'name' => 'Express 10 Parcel'],
                                ['value' => 'PFWEXP00AMPA', 'name' => 'Express AM Parcel'],
                                ['value' => 'PFWEXP0024PA', 'name' => 'Express 24 Parcel'],
                                ['value' => 'PFWEXP0048PA', 'name' => 'Express 48 Parcel'],
                                ['value' => 'PFWEXPLG48PA', 'name' => 'Express 48 Large Parcel'],
                                ['value' => 'PFWGLOBEXPPA', 'name' => 'Global Express Parcel'],
                                ['value' => 'PFWEUPHOMEPA', 'name' => 'Europriority Home Parcel'],
                                ['value' => 'PFWEUPBUSSPA', 'name' => 'Europriority Business Parcel'],
                                ['value' => 'PFWGLOPRI0PA', 'name' => 'Global Priority Parcel'],
                                ['value' => 'PFWGLOVAL0PA', 'name' => 'Global Value Parcel']
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                            'links' => [
                                [
                                    'description' => 'Follow this guide to get your credentials',
                                    'source' => 'http://help.orderhub.io/hc/en-us/articles/207220525-Parcelforce',
                                ],
                            ],
                        ],
                        */
                        [
                            'channelName' => DataplugCarriers::TNT,
                            'displayName' => 'TNT',
                            'code' => DataplugCarrier\Tnt::CODE,
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Domestic Account no'],
                                ['name' => 'International Account no'],
                                ['name' => 'ExpressConnect User'],
                                ['name' => 'ExpressConnect Password', 'inputType' => 'password'],
                                ['name' => 'ExpressLabel User'],
                                ['name' => 'ExpressLabel Password', 'inputType' => 'password'],
                            ],
                            'services' => [
                                ['value' => 'TNTEXP0000PA', 'name' => 'Express Parcel'],
                                ['value' => 'TNTEXPECONPA', 'name' => 'Economy Express Parcel'],
                                ['value' => 'TNTNEXTDAYPA0900', 'name' => 'Next Day 09:00am Parcel'],
                                ['value' => 'TNTNEXTDAYPA1000', 'name' => 'Next Day 10:00am Parcel'],
                                ['value' => 'TNTNEXTDAYPA1200', 'name' => 'Next Day 12:00noon Parcel'],
                                ['value' => 'TNTNEXTDAYPA1700', 'name' => 'Next Day 17:00pm Parcel'],
                                ['value' => 'TNTSAT0000PA', 'name' => 'Saturday Parcel'],
                                ['value' => 'TNTSAT0000PA0900', 'name' => 'Saturday 09:00am Parcel'],
                                ['value' => 'TNTSAT0000PA1000', 'name' => 'Saturday 10:00am Parcel'],
                                ['value' => 'TNTSAT0000PA1200', 'name' => 'Saturday 12:00noon Parcel'],
                                ['value' => 'TNTEXP0000PA0900', 'name' => '09:00am Parcel'],
                                ['value' => 'TNTEXP0000PA1000', 'name' => '10:00am Parcel'],
                                ['value' => 'TNTEXP0000PA1200', 'name' => '12:00noon Parcel'],
                                ['value' => 'TNTEXPECONPA1200', 'name' => '12:00noon Economy Parcel']
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                                'deliveryInstructions' => false,
                            ],
                            'links' => [
                                [
                                    'description' => 'Follow this guide to get your credentials',
                                    'source' => 'http://help.orderhub.io/hc/en-us/articles/206521389-TNT',
                                ],
                            ],
                        ],
                        [
                            'channelName' => DataplugCarriers::UK_MAIL,
                            'displayName' => 'UK Mail',
                            'code' => DataplugCarrier\UkMail::CODE,
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Account no (Item Rates)'],
                                ['name' => 'Account no (KG Rates)'],
                                ['name' => 'User ID'],
                                ['name' => 'Password', 'inputType' => 'password']
                            ],
                            'services' => [
                                ['value' => 'UKMNEXTDAYPA', 'name' => 'Next Working Day'],
                                ['value' => 'UKMNEXTDAYPA1200', 'name' => 'Next Working Day 12:00'],
                                ['value' => 'UKMNEXTDAYPA1030', 'name' => 'Next Working Day 10:30'],
                                ['value' => 'UKMNEXTDAYPA0900', 'name' => 'Next Working Day 09:00'],
                                ['value' => 'UKMPREMR48PA', 'name' => '48 Hour Parcel'],
                                ['value' => 'UKMSATUDAYPA', 'name' => 'Saturday'],
                                ['value' => 'UKMSATUDAYPA1030', 'name' => 'Saturday 10:30'],
                                ['value' => 'UKMSATUDAYPA0900', 'name' => 'Saturday 09:00'],
                                /*
                                // Not using these for now, may add them in later
                                ['value' => 'UKMEXP4800PA', 'name' => 'Parcel 48 Hr'],
                                ['value' => 'UMKEXP7200PA', 'name' => 'Parcel 72 Hour'],
                                ['value' => 'UKMNEXTDAYSB', 'name' => 'Small Bag it Next Day'],
                                ['value' => 'UKMNEXTDAYSB1200', 'name' => 'Small Bag it Next Day by 12:00noon'],
                                ['value' => 'UKMNEXTDAYSB1030', 'name' => 'Small Bag it Next Day by 10:30am'],
                                ['value' => 'UKMNEXTDAYSB0900', 'name' => 'Small Bag it Next day by 09:00am'],
                                ['value' => 'UKMSAT0000SB', 'name' => 'Small Bag it Saturday'],
                                ['value' => 'UKMNEXTDAYMB', 'name' => 'Medium Bag it Next Day'],
                                ['value' => 'UKMNEXTDAYMB1200', 'name' => 'Medium Bag it Next Day by 12:00noon'],
                                ['value' => 'UKMNEXTDAYMB1030', 'name' => 'Medium Bag it Next Day by 10:30am'],
                                ['value' => 'UKMNEXTDAYMB0900', 'name' => 'Medium Bag it Next day by 09:00am'],
                                ['value' => 'UKMSAT0000MB', 'name' => 'Medium Bag it Saturday'],
                                ['value' => 'UKMNEXTDAYLB', 'name' => 'Large Bag it Next Day'],
                                ['value' => 'UKMNEXTDAYLB1200', 'name' => 'Large Bag it Next Day by 12:00noon'],
                                ['value' => 'UKMNEXTDAYLB1030', 'name' => 'Large Bag it Next Day by 10:30am'],
                                ['value' => 'UKMNEXTDAYLB0900', 'name' => 'Large Bag it Next day by 09:00am'],
                                ['value' => 'UKMSAT0000LB', 'name' => 'Large Bag it Saturday'],
                                ['value' => 'UKMNEXTDAYXL', 'name' => 'Extra Large Bag it Next Day'],
                                ['value' => 'UKMNEXTDAYXL1200', 'name' => 'Extra Large Bag it Next Day by 12:00noon'],
                                ['value' => 'UKMNEXTDAYXL1030', 'name' => 'Extra Large Bag it Next Day by 10:30am'],
                                ['value' => 'UKMNEXTDAYXL0900', 'name' => 'Extra Large Bag it Next day by 09:00am'],
                                ['value' => 'UKMSAT0000XL', 'name' => 'Extra Large Bag it Saturday'],
                                */
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                                'deliveryInstructions' => false,
                            ],
                            'links' => [
                                [
                                    'description' => 'Follow this guide to get your credentials',
                                    'source' => 'http://help.orderhub.io/hc/en-us/articles/206521539-UKMail',
                                ],
                            ],
                        ],
                        [
                            'channelName' => DataplugCarriers::UPS,
                            'displayName' => 'UPS',
                            'code' => DataplugCarrier\Ups::CODE,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Book no'],
                                ['name' => 'Username'],
                                ['name' => 'Password', 'inputType' => 'password'],
                                ['name' => 'Prefix'],
                                ['name' => 'Start Sequence'],
                                ['name' => 'End Sequence'],
                            ],
                            'services' => [
                                ['value' => 'UPSEXPRESSPA', 'name' => 'Express Parcel'],
                                ['value' => 'UPSEXPRESSLT', 'name' => 'Express Letter'],
                                ['value' => 'UPSEXPRESSPK', 'name' => 'Express Pak'],
                                ['value' => 'UPSEXPSAVEPA', 'name' => 'Express Saver Parcel'],
                                ['value' => 'UPSEXPSAVELT', 'name' => 'Express Saver Letter'],
                                ['value' => 'UPSEXPSAVEPK', 'name' => 'Express Saver Pak'],
                                ['value' => 'UPSSTANSINPA', 'name' => 'Standard Single Parcel'],
                                ['value' => 'UPSSTANMTIPA', 'name' => 'Standard Multi Parcel'],
                                ['value' => 'UPSEXPITEDPA', 'name' => 'Expedited Parcel'],
                                ['value' => 'UPSEXPPLUSPA', 'name' => 'Express Plus Parcel'],
                                ['value' => 'UPSEXPPLUSLT', 'name' => 'Express Plus Letter'],
                                ['value' => 'UPSEXPPLUSPK', 'name' => 'Express Plus Pak'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'deliveryInstructions' => false,
                            ],
                            'links' => [
                                [
                                    'description' => 'Follow this guide to get your credentials',
                                    'source' => 'http://help.orderhub.io/hc/en-us/articles/207220635-UPS',
                                ],
                            ],
                        ],
                        [
                            'channelName' => DataplugCarriers::YODEL,
                            'displayName' => 'Yodel',
                            'code' => DataplugCarrier\Yodel::CODE,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Meter no'],
                                ['name' => 'Contract no'],
                                ['name' => 'Schedule no'],
                                ['name' => 'Username'],
                                ['name' => 'Password', 'inputType' => 'password'],
                                ['name' => 'Licence Plate Prefix', 'inputType' => 'hidden', 'value' => 'JJD00022'],
                                ['name' => 'Start Sequence'],
                                ['name' => 'End Sequence'],
                            ],
                            'services' => [
                                ['value' => 'YDLPRIO000PA1200', 'name' => 'Priority 12:00noon Parcel'],
                                ['value' => 'YDLEXP0024PA', 'name' => 'Express 24 Parcel'],
                                ['value' => 'YDLEXP0048PA', 'name' => 'Express 48 Parcel'],
                                ['value' => 'YDLSATPRIOPA1200', 'name' => 'Saturday Priority 12:00noon Parcel'],
                                ['value' => 'YDLEXPNI24PA', 'name' => 'Express 24 (NI) Parcel'],
                                ['value' => 'YDLEXPIS24PA', 'name' => 'Express (Isle) Parcel'],
                                ['value' => 'YDLEXPRT24PA', 'name' => 'Express 24 Return Parcel'],
                                ['value' => 'YDLPRIONI0PA1000', 'name' => 'Priority 10:00am (NI) Parcel'],
                                ['value' => 'YDLPRIO000PA1000', 'name' => 'Priority 10:00am Parcel'],
                                ['value' => 'YDLSAT0000PA1000', 'name' => 'Saturday 10:00am Parcel'],
                                ['value' => 'YDLEXPNT24PA', 'name' => 'Express 24 (NI INT) Parcel'],
                                ['value' => 'YDLEXPUK24PA', 'name' => 'Express 24 (UK) Parcel'],
                                ['value' => 'YDLEXPUK48PA', 'name' => 'Express 48 (UK) Parcel'],
                                ['value' => 'YDLHOMNI24PA', 'name' => '@Home 24 (NI) Parcel'],
                                ['value' => 'YDLHOMNI48PA', 'name' => '@Home 48 (NI) Parcel'],
                                ['value' => 'YDLHOMBT24PA', 'name' => '@Home 24 (BT) Parcel'],
                                ['value' => 'YDLHOMBT48PA', 'name' => '@Home 48 (BT) Parcel'],
                                ['value' => 'YDLHOMCAT0PA', 'name' => '@Home 24 Catalogue Parcel'],
                                ['value' => 'YDLHOM0072PA', 'name' => '@Home 72 Parcel '],
                                ['value' => 'YDLHOMNI72PA', 'name' => '@Home 72 (NI) Parcel'],
                                ['value' => 'YDLEXPNI48PA', 'name' => 'Express 48 (NI) Parcel'],
                                ['value' => 'YDLHOM0024PA', 'name' => '@Home 24 Parcel'],
                                ['value' => 'YDLHOM0048PA', 'name' => '@Home 48 Parcel'],
                                ['value' => 'YDLHOMRET0PA', 'name' => '@Home Return Parcel'],
                                ['value' => 'YDLGRN0000PA', 'name' => 'GRN Next Day Parcel'],
                                ['value' => 'YDLSTEC000PA', 'name' => 'STEC Parcel'],
                                ['value' => 'YDLSRTN000PA', 'name' => 'SRTN Parcel'],
                                ['value' => 'YDLSATEXP0PA', 'name' => 'Express Saturday Parcel'],
                                ['value' => 'YDLSATHOM0PA', 'name' => '@Home Saturday Parcel']
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                                'deliveryInstructions' => false,
                            ],
                            'links' => [
                                [
                                    'description' => 'Follow this guide to get your credentials',
                                    'source' => 'http://help.orderhub.io/hc/en-us/articles/207220655-Yodel',
                                ],
                            ],
                        ],
                    ],
                    'defaultOptions' => [
                        'parcels' => true,
                        'collectionDate' => true,
                        'weight' => true,
                        'height' => true,
                        'width' => true,
                        'length' => true,
                        'insurance' => true,
                        'insuranceMonetary' => true,
                        'signature' => true,
                        'deliveryInstructions' => true,
                    ]
                ]
            ],
            NetDespatchShippingService::class => [
                'parameters' => [
                    'defaultDomesticServices' => [
                        // These codes are prefixes, more characters will be added based on chosen options
                        '24' => '24',
                        '48' => '48',
                        '24F' => '24 (Flat)',
                        '48F' => '48 (Flat)',
                        'TPN01' => '24 Tracked',
                        'TPS01' => '48 Tracked',
                        'RMSD9' => 'Special Delivery 9am',
                        'RMSD1' => 'Special Delivery 1pm',
                        'STL1'  => '1st Class (STL)',
                        'STL2'  => '2nd Class (STL)',
                    ],
                    'defaultInternationalServices' => [
                        // These codes are the complete codes
                        'DE1E' => 'Bus Pcls Zero HV RDC Prty',
                        'DE3E' => 'Bus Pcls Zero HV RDC Econ',
                        'DE4E' => 'Bus Pcls Zero LV Prty',
                        'DE6E' => 'Bus Pcls Zero LV Econ',
                        'DG1G' => 'Bus Mail LL Ctry HV RDC Prty',
                        'DG3G' => 'Bus Mail LL Ctry HV RDC Econ',
                        'DG4G' => 'Bus Mail LL Ctry LV Prty',
                        'DG6G' => 'Bus Mail LL Ctry LV Econ',
                        'DW1E' => 'Bus Pcls Bespoke',
                        'IE1E' => 'Bus Pcls Zone Prty',
                        'IE3E' => 'Bus Pcls Zone Econ',
                        'IG1G' => 'Bus Mail LL Zone Prty',
                        'IG3G' => 'Bus Mail LL Zone Econ',
                        'IG4G' => 'Bus Mail LL Zone Prty Mch',
                        'IG6G' => 'Bus Mail LL Zone Econ Mch',
                        'MB1N' => 'Bus Pcls Print Direct Prty',
                        'MB2N' => 'Bus Pcls Print Direct Std',
                        'MB3N' => 'Bus Pcls Print Direct Econ',
                        'MP0E' => 'Bus Pcls Signed +Comp Ctry',
                        'MP1E' => 'Bus Pcls Tracked',
                        'MP4E' => 'Bus Pcls Tracked +Comp',
                        'MP5E' => 'Bus Pcls Signed',
                        'MP6E' => 'Bus Pcls Signed +Comp',
                        'MP7E' => 'Bus Pcls Tracked Ctry',
                        'MP8E' => 'Bus Pcls Tracked +Comp Ctry',
                        'MP9E' => 'Bus Pcls Signed Ctry',
                        'MTAE' => 'Bus Pcls TandS',
                        'MTBE' => 'Bus Pcls TandS +Comp',
                        'MTCG' => 'Bus Mail TandS LL',
                        'MTCP' => 'Bus Mail TandS Letter',
                        'MTDG' => 'Bus Mail TandS +Comp LL',
                        'MTDP' => 'Bus Mail TandS +Comp Letter',
                        'MTEE' => 'Bus Pcls TandS Ctry',
                        'MTFE' => 'Bus Pcls TandS +Comp Ctry',
                        'MTGG' => 'Bus Mail TandS Ctry LL',
                        'MTHG' => 'Bus Mail TandS +Comp Ctry LL',
                        'MTIG' => 'Bus Mail Tracked LL',
                        'MTIP' => 'Bus Mail Tracked Letter',
                        'MTJG' => 'Bus Mail Tracked +Comp LL',
                        'MTJP' => 'Bus Mail Tracked +Comp Letter',
                        'MTKG' => 'Bus Mail Tracked Ctry LL',
                        'MTLG' => 'Bus Mail Tracked +Comp Ctry LL',
                        'MTMG' => 'Bus Mail Signed LL',
                        'MTMP' => 'Bus Mail Signed Letter',
                        'MTNG' => 'Bus Mail Signed +Comp LL',
                        'MTNP' => 'Bus Mail Signed +Comp Letter',
                        'MTOG' => 'Bus Mail Signed Ctry LL',
                        'MTPG' => 'Bus Mail Signed +Comp Ctry LL',
                        'MTQE' => 'Bus Pcls Zone Plus Prty',
                        'MTSE' => 'Bus Pcls Zone Plus Econ',
                        'NDE1E' => 'Test Copy of DE1E Tariff',
                        'OLAE' => 'Std on Account Pcls',
                        'OLAG' => 'Std on Acct LL',
                        'OLAH' => 'Std on Account PrtPaper',
                        'OLAP' => 'Std on Account Letter',
                        'OLSE' => 'Econ on Account Pcls',
                        'OLSG' => 'Econ on Acct LL',
                        'OLSH' => 'Econ on Acct PrtPaper',
                        'OLSP' => 'Econ on Account Letter',
                        'OSAE' => 'Signed on Acct Pcls',
                        'OSAG' => 'Signed on Acct LL',
                        'OSAH' => 'Signed on Acct PrtPaper',
                        'OSAP' => 'Signed on Acct Letter',
                        'OSBE' => 'Signed on Acct +Comp Pcls',
                        'OSBG' => 'Signed on Acct +Comp LL',
                        'OSBH' => 'Signed on Acct +Comp PrtPaper',
                        'OSBP' => 'Signed on Acct +Comp Letter',
                        'OTAE' => 'Tracked on Acct Pcls',
                        'OTAG' => 'Tracked on Acct LL',
                        'OTAH' => 'Tracked on Acct PrtPaper',
                        'OTAP' => 'Tracked on Acct Letter',
                        'OTBE' => 'Tracked on Acct +Comp Pcls',
                        'OTBG' => 'Tracked on Acct +Comp LL',
                        'OTBH' => 'Tracked on Acct +Comp PrtPaper',
                        'OTBP' => 'Tracked on Acct +Comp Letter',
                        'OTCE' => 'TandS on Acct Pcls',
                        'OTCG' => 'TandS on Acct LL',
                        'OTCH' => 'TandS on Acct PrtPaper',
                        'OTCP' => 'TandS on Acct Letter',
                        'OTDE' => 'TandS on Acct +Comp Pcls',
                        'OTDG' => 'TandS on Acct +Comp LL',
                        'OTDH' => 'TandS on Acct +Comp PrtPaper',
                        'OTDP' => 'TandS on Acct +Comp Letter',
                        'OZ1N' => 'Bus Mail Mixd Prty',
                        'OZ3N' => 'Bus Mail Mixd Econ',
                        'OZ4N' => 'Bus Mail Mixd Prty Mch',
                        'OZ6N' => 'Bus Mail Mixd Econ Mch',
                        'PS0E' => 'Bus Pcls Max Econ',
                        'PS7G' => 'Bus Mail LL Max Prty',
                        'PS8G' => 'Bus Mail LL Max Econ',
                        'PS9E' => 'Bus Pcls Max Prty',
                        'PSBG' => 'Bus Mail LL Max Std',
                        'PSCE' => 'Bus Pcls Max Std',
                        'WE1E' => 'Bus Pcls Zero Prty',
                        'WE3E' => 'Bus Pcls Zero Econ',
                        'WG1G' => 'Bus Mail LL Zero Prty',
                        'WG3G' => 'Bus Mail LL Zero Econ',
                        'WG4G' => 'Bus Mail LL Zero Prty Mch',
                        'WG6G' => 'Bus Mail LL Zero Econ Mch',
                        'WW1N' => 'Bus Mail Mixd Zero Prty',
                        'WW3N' => 'Bus Mail Mixd Zero Econ',
                        'WW4N' => 'Bus Mail Mixd Zero Prty Mch',
                        'WW6N' => 'Bus Mail Mixd Zero Econ Mch',
                    ]
                ]
            ],
            NetDespatchShippingOptionsProvider::class => [
                'parameters' => [
                    'carrierBookingOptionsDomestic' => [
                        'parcels' => false,
                        'collectionDate' => false,
                        'weight' => true,
                        'packageType' => true,
                        'addOns' => true,
                        'height' => false,
                        'width' => false,
                        'length' => false,
                        'tradeTariffCode' => false,
                        'insurance' => false,
                        'insuranceMonetary' => false,
                        'signature' => false,
                        'deliveryInstructions' => true,
                        'itemParcelAssignment' => false,
                    ],
                    'carrierBookingOptionsInternational' => [
                        'parcels' => false,
                        'collectionDate' => false,
                        'weight' => true,
                        'packageType' => false,
                        'addOns' => false,
                        'height' => true,
                        'width' => true,
                        'length' => true,
                        'tradeTariffCode' => true,
                        'insurance' => false,
                        'insuranceMonetary' => false,
                        'signature' => false,
                        'deliveryInstructions' => true,
                        'itemParcelAssignment' => true,
                    ],
                    'serviceOptions' => [
                        '24' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        '48' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        '24F' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        '48F' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        'TPN01' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature', 'excludes' => 'Safe Place'],
                                ['title' => 'Safe Place', 'excludes' => 'Signature'],
                            ]
                        ],
                        'TPS01' => [
                            'packageTypes' => ['Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature', 'excludes' => 'Safe Place'],
                                ['title' => 'Safe Place', 'excludes' => 'Signature'],
                            ]
                        ],
                        'RMSD1' => [
                            'packageTypes' => ['Parcel'],
                            'addOns' => [
                                ['title' => 'Saturday'],
                                ['title' => 'Insurance £500', 'excludes' => 'Insurance £1000,Insurance £2500'],
                                ['title' => 'Insurance £1000', 'excludes' => 'Insurance £500,Insurance £2500'],
                                ['title' => 'Insurance £2500', 'excludes' => 'Insurance £500,Insurance £1000'],
                            ]
                        ],
                        'RMSD9' => [
                            'packageTypes' => ['Parcel'],
                            'addOns' => [
                                ['title' => 'Saturday'],
                                ['title' => 'Insurance £50', 'excludes' => 'Insurance £1000,Insurance £2500'],
                                ['title' => 'Insurance £1000', 'excludes' => 'Insurance £50,Insurance £2500'],
                                ['title' => 'Insurance £2500', 'excludes' => 'Insurance £50,Insurance £1000'],
                            ]
                        ],
                        'STL1'  => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                        'STL2'  => [
                            'packageTypes' => ['Letter', 'Large Letter', 'Parcel'],
                            'addOns' => [
                                ['title' => 'Signature'],
                            ]
                        ],
                    ]
                ]
            ],
            NetDespatchOrderCreateService::class => [
                'parameters' => [
                    // Don't use our FailoverClient, use Guzzle directly, as this is for talking to a third-party
                    'guzzleClient' => \Guzzle\Http\Client::class,
                ]
            ],
            NetDespatchAccountCreationService::class => [
                'parameters' => [
                    'cryptor' => 'netdespatch_cryptor',
                ]
            ],
            CustomerCountRepository::class => [
                'parameters' => [
                    'storage' => CustomerCountCacheStorage::class,
                    'repository' => CustomerCountOrderLookupStorage::class,
                ],
            ],
            CustomerCountCacheStorage::class => [
                'parameters' => [
                    'client' => 'reliable_redis',
                ],
            ],
            AmazonShippingServiceApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ],
            ],
            AmazonShippingServiceService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            AmazonCarrierService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
        ),
    ),
    'view_manager' => [
        'strategies' => [
            'CG_Mustache\View\Strategy'
        ],
    ],
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'router' => [
        'routes' => [
            UI::NAVIGATION_ROUTE => [
                'options' => [
                    'defaults' => [
                        PermissionService::ROUTE_WHITELIST => true,
                    ],
                ]
            ],
        ],
    ],
    'CG_Login' => [
        'CG_Login' => [
            'default_landing_route_registered' => SetupWizard\Module::ROUTE
        ]
    ]
);

$configFiles = glob(__DIR__ . '/global/*.php');
foreach ($configFiles as $configFile) {
    $configFileContents = require_once $configFile;
    $config = \Zend\Stdlib\ArrayUtils::merge($config, $configFileContents);
}
return $config;
