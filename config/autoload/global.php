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
use CG\Order\Client\Storage\Api as OrderApiClient;
use CG\Order\Client\Item\Storage\Api as ItemApiClient;
use CG\Order\Client\Tag\Storage\Api as OrderTagApiClient;
use CG\Order\Client\Batch\Storage\Api as OrderBatchApiClient;
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
use CG\Order\Shared\InvoiceEmailer\Service as InvoiceEmailerService;

use CG\Location\StorageInterface as LocationStorage;
use CG\Location\Storage\Api as LocationApiStorage;

use CG\Stock\Location\StorageInterface as StockLocationStorage;
use CG\Stock\Location\Storage\Api as StockLocationApiStorage;

use CG\Billing\Transaction\StorageInterface as TransactionStorage;
use CG\Billing\Transaction\Storage\Api as TransactionApiStorage;
use CG\Billing\BillingWindow\Storage\Api as BillingWindowStorage;
use CG\Billing\BillingWindow\Service as BillingWindowService;

use CG_UI\Module as UI;
use CG_Permission\Service as PermissionService;
use CG\Stock\Audit\Storage\Queue as StockAuditQueue;

use CG_SSO\Module as SsoModule;

// Logging
use CG\Log\Shared\Storage\Redis\Channel as RedisChannel;
use CG\Log\Psr\Logger as CGPsrLogger;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

use CG\OrganisationUnit\Service as OrganisationUnitService;

//Order Counts
use CG\Order\Shared\OrderCounts\Storage\Api as OrderCountsApi;
// Order usage
use CG_Usage\Service as UsageService;
use CG\UsageCheck\Service as UsageCheckService;

// Settings
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Client\Storage\Api as InvoiceSettingsApiStorage;
use CG\Settings\Product\StorageInterface as ProductSettingsStorage;
use CG\Settings\Product\Storage\Api as ProductSettingsStorageApi;
use CG\Settings\InvoiceMapping\Service as InvoiceMappingService;
use CG\Settings\InvoiceMapping\Mapper as InvoiceMappingMapper;
use CG\Settings\InvoiceMapping\Storage\Api as InvoiceMappingStorageApi;

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
use CG\Ekm\Product\TaxRate\StorageInterface as EkmTaxRateStorage;
use CG\Ekm\Product\TaxRate\Repository as EkmTaxRateRepository;
use CG\Ekm\Product\TaxRate\Importer\Soap as EkmSoapTaxRateImporter;

// Stock Import
use CG\Stock\Import\File\StorageInterface as StockImportInterface;
use CG\Stock\Import\File\Storage\S3 as StockImportFileS3;
use CG\FileStorage\S3\Adapter as S3FileImportAdapter;
use CG\Stock\Import\File\Mapper as StockImportFileMapper;

// ApiCredentials
use CG\ApiCredentials\StorageInterface as ApiCredentialsStorage;
use CG\ApiCredentials\Storage\Api as ApiCredentialsApi;

// Invoice Image Client
use CG\Template\Image\ClientInterface as ImageTemplateClient;
use CG\Template\Image\Client\Redis as ImageTemplateRedisClient;
use CG\Template\Image\Client\Guzzle as ImageTemplateGuzzleClient;

// Couriers
use CG\Order\Shared\Label\StorageInterface as OrderLabelStorage;
use CG\Order\Client\Label\Storage\Api as OrderLabelApiStorage;
use CG\Account\Shared\Manifest\StorageInterface as AccountManifestStorage;
use CG\Account\Client\Manifest\Storage\Api as AccountManifestApiStorage;

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

// Accounts
use CG\Account\Client\StorageInterface as AccountStorage;
use CG\Account\Client\Storage\Api as AccountApiStorage;

use CG\Stdlib\SoapClient as CGSoapClient;

// Account Request
use CG\Account\Request\StorageInterface as AccountRequestStorage;
use CG\Account\Request\Storage\Api as AccountRequestApiStorage;

// ShipmentMetadata
use CG\Order\Shared\ShipmentMetadata\StorageInterface as ShipmentMetadataStorage;
use CG\Order\Shared\ShipmentMetadata\Storage\Api as ShipmentMetadataApiStorage;

use CG\Billing\Token\StorageInterface as TokenStorageInterface;
use CG\Billing\Token\Storage\Api as TokenStorageApi;

//  Purchase Order
use CG\PurchaseOrder\StorageInterface as PurchaseOrderStorage;
use CG\PurchaseOrder\Storage\Api as PurchaseOrderApiStorage;
use CG\PurchaseOrder\Item\StorageInterface as PurchaseOrderItemStorage;
use CG\PurchaseOrder\Item\Storage\Api as PurchaseOrderItemApiStorage;

//  Feature Flags
use Opensoft\Rollout\Storage\RedisStorageAdapter as RolloutRedisStorage;
use Opensoft\Rollout\Storage\StorageInterface as RolloutStorage;

// ExchangeRate
use CG\ExchangeRate\Storage\Api as ExchangeRateApiStorage;
use CG\ExchangeRate\StorageInterface as ExchangeRateStorage;

// Sites
use CG\Stdlib\Sites;

// Package Rules
use CG\Settings\PackageRules\StorageInterface as PackageRulesStorage;
use CG\Settings\PackageRules\Storage\Api as PackageRulesApiStorage;

// Shipping Ledger
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use CG\Billing\Shipping\Ledger\Storage\Api as ShippingLedgerApi;
use CG\Billing\Shipping\Ledger\Mapper as ShippingLedgerMapper;

use CG\Email\Smtp;

use Zend\Captcha\ReCaptcha as ReCaptcha;

$config = array(
    'di' => array(
        'instance' => array(
            'preferences' => array(
                ExchangeRateStorage::class => ExchangeRateApiStorage::class,
                EventManagerInterface::class => EventManager::class,
                OrderStorage::class => OrderApiClient::class,
                ItemStorage::class => ItemApiClient::class,
                OrderClientStorage::class => OrderApiClient::class,
                OrderTagStorage::class => OrderTagApiClient::class,
                OrderBatchStorage::class => OrderBatchApiClient::class,
                SessionManagerInterface::class => SessionManager::class,
                ServiceLocatorInterface::class => ServiceManager::class,
                StockLocationStorage::class => StockLocationApiStorage::class,
                TransactionStorage::class => TransactionApiStorage::class,
                DiscountStorage::class => DiscountApiStorage::class,
                SubscriptionDiscountStorage::class => SubscriptionDiscountApiStorage::class,
                ApiCredentialsStorage::class => ApiCredentialsApi::class,
                ImageTemplateClient::class => ImageTemplateRedisClient::class,
                ProductSettingsStorage::class => ProductSettingsStorageApi::class,
                OrderLabelStorage::class => OrderLabelApiStorage::class,
                AccountManifestStorage::class => AccountManifestApiStorage::class,
                TransactionClient::class => RedisTransactionClient::class,
                StockLogStorage::class => StockLogApiStorage::class,
                UsageService::class => 'order_count_usage_service',
                UsageCheckService::class => 'order_count_usage_check_service',
                CustomerCountStorage::class => CustomerCountRepository::class,
                LockingStorage::class => LockingRedisStorage::class,
                AccountStorage::class => AccountApiStorage::class,
                AccountRequestStorage::class => AccountRequestApiStorage::class,
                PsrLoggerInterface::class => CGPsrLogger::class,
                ShipmentMetadataStorage::class => ShipmentMetadataApiStorage::class,
                TokenStorageInterface::class => TokenStorageApi::class,
                PurchaseOrderStorage::class => PurchaseOrderApiStorage::class,
                PurchaseOrderItemStorage::class => PurchaseOrderItemApiStorage::class,
                RolloutStorage::class => RolloutRedisStorage::class,
                StockImportInterface::class => StockImportFileS3::class,
                LocationStorage::class => LocationApiStorage::class,
                PackageRulesStorage::class => PackageRulesApiStorage::class,
                Smtp::class => 'orderhub-smtp',
                EkmTaxRateStorage::class => EkmTaxRateRepository::class,
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
                'EUVATCodeCheckerSoapClient' => CGSoapClient::class,
                'StockImportS3FileImportAdapter' => S3FileImportAdapter::class,
            ],
            ExchangeRateApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            RolloutRedisStorage::class => [
                'parameters' => [
                    'redis' => 'reliable_redis',
                ]
            ],
            'EUVATCodeCheckerSoapClient' => [
                'parameter' => [
                    'wsdl' => 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl',
                    'options' => ['exceptions' => true, 'trace' => true]
                ]
            ],
            'amazonWriteCGSql' => [
                'parameter' => [
                    'adapter' => 'amazonWrite'
                ]
            ],
            PurchaseOrderApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            PurchaseOrderItemApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
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
            InvoiceMappingStorageApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                    'mapper' => InvoiceMappingMapper::class
                ]
            ],
            InvoiceMappingService::class => [
                'parameters' => [
                    'repository' => InvoiceMappingStorageApi::class,
                    'mapper' => InvoiceMappingMapper::class
                ]
            ],
            LocationApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            StockLocationApiStorage::class => [
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
            EkmSoapTaxRateImporter::class => [
                'parameters' => [
                    'cryptor' => 'ekm_cryptor',
                    'repository' => EkmTaxRateRepository::class
                ]
            ],
            StockImportFileS3::class => [
                'parameter' => [
                    'mapper' => StockImportFileMapper::class,
                    's3FileStorage' => 'StockImportS3FileImportAdapter'
                ]
            ],
            'StockImportS3FileImportAdapter' => [
                'parameter' => [
                    'location' => function() { return StockImportFileS3::S3_BUCKET; }
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
            PackageRulesApiStorage::class => [
                'parameters' => [
                    'client' => 'billing_guzzle'
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
            AccountRequestApiStorage::class => [
                'parameters' => [
                    'client' => 'account_guzzle'
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
            InvoiceEmailerService::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis',
                ],
            ],
            ShipmentMetadataApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            TokenStorageApi::class => [
                'parameters' => [
                    'client' => 'billing_guzzle'
                ]
            ],
            Sites::class => [
                'parameters' => [
                    'config' => 'app_config'
                ]
            ],
            ImageTemplateRedisClient::class => [
                'parameters' => [
                    'redis' => 'image-cache_redis',
                    'fallbackClient' => ImageTemplateGuzzleClient::class,
                ],
            ],
            ImageTemplateGuzzleClient::class => [
                'parameters' => [
                    'client' => 'imagetemplate_guzzle',
                ],
            ],
            ShippingLedgerService::class => [
                'parameters' => [
                    'repository' => ShippingLedgerApi::class,
                    'mapper' => ShippingLedgerMapper::class
                ]
            ],
            ShippingLedgerApi::class => [
                'parameters' => [
                    'client' => 'billing_guzzle',
                ]
            ],
            ListingApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            SetPrintedDate::class => [
                'parameters' => [
                    'gearmanClient' => 'orderGearmanClient'
                ]
            ],
            PermissionService::class => [
                'parameters' => [
                    'ouService' => 'organisationUnitApcReadService',
                    'partnerManagedAdditionalRouteWhiteList' => [
                        SsoModule::ROUTE_LOGOUT => SsoModule::ROUTE_LOGOUT,
                        SsoModule::ROUTE_RETURN => SsoModule::ROUTE_RETURN
                    ]
                ]
            ],


            ReCaptcha::class =>  [
                'parameters' => [
                    'options' => [
                        'pubKey' => '6LdDo1oaAAAAAGPdqiUwIu5K304QGEPtQeC-xDmZ',
                        'privKey' => '6LdDo1oaAAAAADFajK5SEEhwWwhghOyW7lBQaw-p'
                    ]
                ]
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
