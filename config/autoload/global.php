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
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Client\Storage\Api as InvoiceSettingsApiStorage;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\UserPreference\Client\Storage\Api as UserPreferenceStorage;
use Zend\Session\ManagerInterface as SessionManagerInterface;
use Zend\Session\SessionManager;
use Orders\Order\Batch\Service as OrderBatchService;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;
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

use CG\Log\Shared\Storage\Redis\Channel as RedisChannel;

use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitStorageApi;

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

// Dataplug
use CG\Dataplug\Carrier\Service as DataplugCarrierService;
use CG\Channel\ShippingOptionsProviderInterface as ChannelShippingOptionsProviderInterface;
use CG_Dataplug\Controller\AccountController as DataplugAccountController;
use Settings\Module as SettingsModule;
use Settings\Controller\ChannelController;
use CG\Order\Shared\Label\StorageInterface as OrderLabelStorage;
use CG\Order\Client\Label\Storage\Api as OrderLabelApiStorage;
use CG\Product\Detail\StorageInterface as ProductDetailStorage;
use CG\Product\Detail\Storage\Api as ProductDetailApiStorage;
use CG\Dataplug\Account\Service as DataplugAccountService;
use CG\Dataplug\Account\Storage\Api as DataplugAccountApi;
use CG\Dataplug\Request\Factory\CreateCarrier as DataplugCreateCarrierRequestFactory;
use CG\Dataplug\Request\Factory\UpdateCarrier as  DataplugUpdateCarrierRequestFactory;
use CG\Dataplug\Carriers;
use CG\Account\Shared\Manifest\StorageInterface as AccountManifestStorage;
use CG\Account\Client\Manifest\Storage\Api as AccountManifestApiStorage;

return array(
    'di' => array(
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
                ChannelShippingOptionsProviderInterface::class => DataplugCarrierService::class,
                OrderLabelStorage::class => OrderLabelApiStorage::class,
                ProductDetailStorage::class => ProductDetailApiStorage::class,
                AccountManifestStorage::class => AccountManifestApiStorage::class,
            ),
            'aliases' => [
                'amazonWriteCGSql' => CGSql::class
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
            DataplugCarrierService::class => [
                'parameters' => [
                    'carriersConfig' => [
                        [
                            'channelName' => Carriers::DHL,
                            'displayName' => 'DHL',
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
                        ],
                        [
                            'channelName' => Carriers::DPD,
                            'displayName' => 'DPD',
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'User ID'],
                                ['name' => 'SLID'],
                                ['name' => 'Authorisation Code'],
                                ['name' => 'Start Sequence'],
                                ['name' => 'End Sequence'],
                            ],
                            'services' => [
                                ['value' => 'DPDEXPR000PA', 'name' => 'Express Parcel'],
                                ['value' => 'DPDEXPDOC0PA', 'name' => 'Express Document'],
                                ['value' => 'DPDEXP00EUPA', 'name' => 'Express EU Parcel'],
                                ['value' => 'DPDTWODAY0PA', 'name' => 'Two Day Parcel'],
                                ['value' => 'DPDNEXTDAYPA', 'name' => 'Next Day Parcel'],
                                ['value' => 'DPDNEXTDAYPA1200', 'name' => 'Next Day 12:00noon Parcel'],
                                ['value' => 'DPDNEXTDAYPA1000', 'name' => 'Next Day 10:00am Parcel'],
                                ['value' => 'DPDSAT0000PA', 'name' => 'Saturday Parcel'],
                                ['value' => 'DPDSAT0000PA1200', 'name' => 'Saturday 12:00noon Parcel'],
                                ['value' => 'DPDSAT0000PA1000', 'name' => 'Saturday 10:00am Parcel'],
                                ['value' => 'DPDCLASSICPA', 'name' => 'Classic Parcel'],
                                ['value' => 'DPDNEXTDAYPK', 'name' => 'Next day Express Pack'],
                                ['value' => 'DPDNEXTDAYPK1200', 'name' => 'Next day 12:00noon Express Pack'],
                                ['value' => 'DPDNEXTDAYPK1000', 'name' => 'Next day 10:00am Express Pack'],
                                ['value' => 'DPDTWODAY0PL', 'name' => 'Two Day Pallet'],
                                ['value' => 'DPDNEXTDAYPL', 'name' => 'Next Day Pallet'],
                                ['value' => 'DPDNEXTDAYPL1200', 'name' => 'Next Day 12:00noon Pallet'],
                                ['value' => 'DPDNEXTDAYPL1000', 'name' => 'Next Day 10:00am Pallet'],
                                ['value' => 'DPDSAT0000PL', 'name' => 'Saturday Pallet'],
                                ['value' => 'DPDSAT0000PL1200', 'name' => 'Saturday 12:00noon Pallet'],
                                ['value' => 'DPDSAT0000PL1000', 'name' => 'Saturday 10:00am Pallet'],
                                ['value' => 'DPDCLASAIRPA', 'name' => 'Classic Air Parcel'],
                                ['value' => 'DPDSAT0000PK', 'name' => 'Saturday Express Pack'],
                                ['value' => 'DPDSAT0000PK1000', 'name' => 'Saturday 10:00am Express Pack'],
                                ['value' => 'DPDSAT0000PK1200', 'name' => 'Saturday 12:00noon Express Pack']
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => Carriers::FEDEX,
                            'displayName' => 'FedEx',
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Key'],
                                ['name' => 'Password', 'inputType' => 'password'],
                                ['name' => 'Trans ID'],
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
                        ],
                        [
                            'channelName' => Carriers::INTERLINK,
                            'displayName' => 'Interlink',
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'User ID'],
                                ['name' => 'Authorisation Code'],
                                ['name' => 'Start Sequence'],
                                ['name' => 'End Sequence'],
                                ['name' => 'Order Start Sequence'],
                                ['name' => 'Order End Sequence']
                            ],
                            'services' => [
                                ['value' => 'INTNEXTDAYPA0930', 'name' => 'Next Day 9.30am Parcel'],
                                ['value' => 'INTNEXTDAYPA1200', 'name' => 'Next Day 12.00noon Parcel'],
                                ['value' => 'INTNEXTDAYPA', 'name' => 'Next Day Parcel'],
                                ['value' => 'INTSATUDAYPA1000', 'name' => 'Saturday 10.00am Parcel'],
                                ['value' => 'INTSATUDAYPA1200', 'name' => 'Saturday 12.00noon Parcel'],
                                ['value' => 'INTTWODAY0PA', 'name' => 'Two Day Parcel'],
                                ['value' => 'INTHOMECALPA', 'name' => 'Homecall Parcel']
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],

                        /*
                        // Not going live with Hermes but will need this in the near future so leaving this here
                        [
                            'channelName' => Carriers::MYHERMES,
                            'displayName' => 'Hermes corporate',
                            'fields' => [
                                ['name' => 'Client ID'],
                                ['name' => 'Username'],
                                ['name' => 'Password', 'inputType' => 'password'],
                            ],
                            'services' => [
                                ['value' => 'HRMNEXT000PA', 'name' => 'Next Day Parcel'],
                                ['value' => 'HRMSTD0000PA', 'name' => 'Standard Parcel'],
                                ['value' => 'HRMNEXTPODPA', 'name' => 'Next Day Proof Of Delivery Parcel'],
                                ['value' => 'HRMSTD0PODPA', 'name' => 'Standard Proof Of Delivery Parcel'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                                'deliveryInstructions' => false,
                            ],
                        ],
                        */

                        [
                            'channelName' => Carriers::PARCELFORCE,
                            'displayName' => 'Parcelforce',
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
                        ],
                        [
                            'channelName' => Carriers::ROYAL_MAIL_OBA,
                            'displayName' => 'Royal Mail OBA',
                            'manifestOncePerDay' => true,
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'PPI Account no'],
                                ['name' => 'Wire no'],
                                ['name' => 'Contract no'],
                                ['name' => 'Hub no'],
                                ['name' => 'Posting Location no'],
                                ['name' => 'Username OBA'],
                                ['name' => 'Password OBA', 'inputType' => 'password'],
                                ['name' => 'Access Code'],
                            ],
                            'services' => [
                                ['value' => 'RMIIE10000PA', 'name' => 'International Business Zone Sort Priority Parcel'],
                                ['value' => 'RMIIG10000LT', 'name' => 'International Business Mail Large Letter Zone Sort Priority Letter'],
                                ['value' => 'RMIIP10000LT', 'name' => 'International Business Mail Letter Zone Sort Priority Letter'],
                                ['value' => 'RMIOLA0000PA', 'name' => 'International Standard On Account Parcel'],
                                ['value' => 'RMIOLA0000LT', 'name' => 'International Standard On Account Letter'],
                                ['value' => 'RMIOLA0000LL', 'name' => 'International Standard On Account Large Letter'],
                                ['value' => 'RMICRL2400LT', 'name' => 'Domestic RM24 Standard Letter'],
                                ['value' => 'RMICRL2400LL', 'name' => 'Domestic RM24 Standard Large Letter'],
                                ['value' => 'RMICRL2400SP', 'name' => 'Domestic RM24 Standard Small Parcel'],
                                ['value' => 'RMICRL2400MP', 'name' => 'Domestic RM24 Standard Medium Parcel'],
                                ['value' => 'RMICRL4800LT', 'name' => 'Domestic RM48 Standard Letter'],
                                ['value' => 'RMICRL4800LL', 'name' => 'Domestic RM48 Standard Large Letter'],
                                ['value' => 'RMICRL4800SP', 'name' => 'Domestic RM48 Standard Small Parcel'],
                                ['value' => 'RMICRL4800MP', 'name' => 'Domestic RM48 Standard Medium Parcel'],
                                ['value' => 'RMISTL1000LT', 'name' => 'Domestic 1st Class Account Mail Letter'],
                                ['value' => 'RMISTL1000LL', 'name' => 'Domestic 1st Class Account Mail Large Letter'],
                                ['value' => 'RMISTL1000SP', 'name' => 'Domestic 1st Class Account Mail Small Parcel'],
                                ['value' => 'RMISTL1000MP', 'name' => 'Domestic 1st Class Account Mail Medium Parcel'],
                                ['value' => 'RMISTL2000LT', 'name' => 'Domestic 2nd Class Account Mail Letter'],
                                ['value' => 'RMISTL2000LL', 'name' => 'Domestic 2nd Class Account Mail Large Letter'],
                                ['value' => 'RMISTL2000SP', 'name' => 'Domestic 2nd Class Account Mail Small Parcel'],
                                ['value' => 'RMISTL2000MP', 'name' => 'Domestic 2nd Class Account Mail Medium Parcel'],
                                /*
                                // The following services are not currently in use as they require extra work but we may add them in later
                                ['value' => 'RMIMP10000PA', 'name' => 'International Business Parcel Tracked'],
                                ['value' => 'RMIMP40000PA', 'name' => 'International Business Parcel Tracked Extra Compensation'],
                                ['value' => 'RMIMP50000PA', 'name' => 'International Business Parcel Signed'],
                                ['value' => 'RMIMP60000PA', 'name' => 'International Business Parcel Signed Extra Compensation'],
                                ['value' => 'RMIMTA0000PA', 'name' => 'International Business Parcel Tracked & Signed'],
                                ['value' => 'RMIMTB0000PA', 'name' => 'International Business Parcel Tracked & Signed Extra Compensation'],
                                ['value' => 'RMIMTC0000LL', 'name' => 'International Business Mail Tracked & Signed Large Letter'],
                                ['value' => 'RMIMTC0000LT', 'name' => 'International Business Mail Tracked & Signed Letter'],
                                ['value' => 'RMIMTD0000LL', 'name' => 'International Business Mail Tracked & Signed Extra Compensation Large Letter'],
                                ['value' => 'RMIMTD0000LT', 'name' => 'International Business Mail Tracked & Signed Extra Compensation Letter'],
                                ['value' => 'RMIMTI0000LT', 'name' => 'International Business Mail Tracked Large Letter'],
                                ['value' => 'RMIMTI0000LL', 'name' => 'International Business Mail Tracked Letter'],
                                ['value' => 'RMIMTJ0000LL', 'name' => 'International Business Mail Tracked Extra Compensation Large Letter'],
                                ['value' => 'RMIMTJ0000LT', 'name' => 'International Business Mail Tracked Extra Compensation Letter'],
                                ['value' => 'RMIMTM0000LL', 'name' => 'International Business Mail Signed Large Letter'],
                                ['value' => 'RMIMTM0000LT', 'name' => 'International Business Mail Signed Letter'],
                                ['value' => 'RMIMTN0000LL', 'name' => 'International Business Mail Signed Extra Compensation Large Letter'],
                                ['value' => 'RMIMTN0000LT', 'name' => 'International Business Mail Signed Extra Compensation Letter'],
                                ['value' => 'RMISD10000LT', 'name' => 'Domestic Special Delivery Guaranteed by 1pm Letter'],
                                ['value' => 'RMISD10000LL', 'name' => 'Domestic Special Delivery Guaranteed by 1pm Large Letter'],
                                ['value' => 'RMISD10000SP', 'name' => 'Domestic Special Delivery Guaranteed by 1pm Small Parcel'],
                                ['value' => 'RMISD10000MP', 'name' => 'Domestic Special Delivery Guaranteed by 1pm Medium Parcel'],
                                ['value' => 'RMISD40000LT', 'name' => 'Domestic Special Delivery Guaranteed by 9am Letter'],
                                ['value' => 'RMISD40000LL', 'name' => 'Domestic Special Delivery Guaranteed by 9am Large Letter'],
                                ['value' => 'RMISD40000SP', 'name' => 'Domestic Special Delivery Guaranteed by 9am Small Parcel'],
                                ['value' => 'RMISD40000MP', 'name' => 'Domestic Special Delivery Guaranteed by 9am Medium Parcel'],
                                ['value' => 'RMICRL24S0LT', 'name' => 'Domestic RM24 Signed For Letter'],
                                ['value' => 'RMICRL24S0LL', 'name' => 'Domestic RM24 Signed For Large Letter'],
                                ['value' => 'RMICRL24S0SP', 'name' => 'Domestic RM24 Signed For Small Parcel'],
                                ['value' => 'RMICRL24S0MP', 'name' => 'Domestic RM24 Signed For Medium Parcel'],
                                ['value' => 'RMICRL48S0LT', 'name' => 'Domestic RM48 Signed For Letter'],
                                ['value' => 'RMICRL48S0LL', 'name' => 'Domestic RM48 Signed For Large Letter'],
                                ['value' => 'RMICRL48S0SP', 'name' => 'Domestic RM48 Signed For Small Parcel'],
                                ['value' => 'RMICRL48S0MP', 'name' => 'Domestic RM48 Signed For Medium Parcel'],
                                ['value' => 'RMIOSA0000PA', 'name' => 'International Signed On Account Parcel'],
                                ['value' => 'RMIOSA0000LT', 'name' => 'International Signed On Account Letter'],
                                ['value' => 'RMIOSA0000LL', 'name' => 'International Signed On Account Large Letter'],
                                ['value' => 'RMIOTA0000PA', 'name' => 'International Tracked On Account Parcel'],
                                ['value' => 'RMIOTA0000LT', 'name' => 'International Tracked On Account Letter'],
                                ['value' => 'RMIOTA0000LL', 'name' => 'International Tracked On Account Large Letter'],
                                ['value' => 'RMIOTC0000PA', 'name' => 'International Tracked & Signed On Account Parcel'],
                                ['value' => 'RMIOTC0000LT', 'name' => 'International Tracked & Signed On Account Letter'],
                                ['value' => 'RMIOTC0000LL', 'name' => 'International Tracked & Signed On Account Large Letter'],
                                */
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => Carriers::TNT,
                            'displayName' => 'TNT',
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
                        ],
                        /*
                        // Not going live with UKMail but will need this in the near future so leaving this here
                        [
                            'channelName' => Carriers::UK_MAIL,
                            'displayName' => 'UK Mail',
                            'allowsCancellation' => false,
                            'allowsManifesting' => false,
                            'fields' => [
                                ['name' => 'Account no (Item Rates)'],
                                ['name' => 'Account no (KG Rates)'],
                                ['name' => 'User ID'],
                                ['name' => 'Password', 'inputType' => 'password']
                            ],
                            'services' => [
                                ['value' => 'UKMNEXTDAYPA', 'name' => 'Parcel Next Day'],
                                ['value' => 'UKMNEXTDAYPA1200', 'name' => 'Parcel Next Day by 12:00noon'],
                                ['value' => 'UKMNEXTDAYPA1030', 'name' => 'Parcel Next Day by 10:30am'],
                                ['value' => 'UKMNEXTDAYPA0900', 'name' => 'Parcel Next day by 09:00am'],
                                ['value' => 'UKMSAT0000PA', 'name' => 'Parcel Saturday'],
                                ['value' => 'UKMSAT0000PA1030', 'name' => 'Parcel Saturday by 10:30am'],
                                ['value' => 'UKMSAT0000PA0900', 'name' => 'Parcel Saturday by 09:00am'],
                                ['value' => 'UKMEXP4800PA', 'name' => 'Parcel 48 Hr'],
                                ['value' => 'UMKEXP7200PA', 'name' => 'Parcel 72 Hour'],
                                ['value' => 'UKMPREM480PA', 'name' => 'Premier Parcel 48 Hour'],
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
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                                'deliveryInstructions' => false,
                            ],
                        ],
                        */
                        [
                            'channelName' => Carriers::UPS,
                            'displayName' => 'UPS',
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
                        ],
                        [
                            'channelName' => Carriers::YODEL,
                            'displayName' => 'Yodel',
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Meter no'],
                                ['name' => 'Contract no'],
                                ['name' => 'Schedule no'],
                                ['name' => 'Username'],
                                ['name' => 'Password', 'inputType' => 'password'],
                                ['name' => 'Licence Plate Prefix'],
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
                        ],
                    ],
                    'defaultOptions' => [
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
            DataplugAccountController::class => [
                'parameters' => [
                    'accountRoute' => implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS])
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
);
