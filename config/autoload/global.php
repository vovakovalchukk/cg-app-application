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

// Dataplug
use CG\Dataplug\Carrier\Service as DataplugCarrierService;
use CG\Channel\ShippingOptionsProviderInterface as ChannelShippingOptionsProviderInterface;
use CG_Dataplug\Controller\AccountController as DataplugAccountController;
use Settings\Module as SettingsModule;
use Settings\Controller\ChannelController;

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
                ChannelShippingOptionsProviderInterface::class => DataplugCarrierService::class,
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
            DataplugCarrierService::class => [
                'parameters' => [
                    'carriersConfig' => [
                        [
                            'channelName' => 'dhl',
                            'displayName' => 'DHL',
                            'fields' => [
                                ['name' => 'Domestic Account no'],
                                ['name' => 'International Account no'],
                                ['name' => 'Site ID'],
                                ['name' => 'Password', 'inputType' => 'password'],
                            ],
                            'services' => [
                                ['name' => 'Express Worldwide Parcel'],
                                ['name' => 'Express Worldwide Docs'],
                                ['name' => 'Economy Select Parcel'],
                                ['name' => 'Express 09:00am Parcel'],
                                ['name' => 'Express 09:00am Docs'],
                                ['name' => 'Express 12:00noon Parcel'],
                                ['name' => 'Express 12:00noon Docs'],
                                ['name' => 'Domestic Express Parcel'],
                                ['name' => 'Domestic Express 09:00am Parcel'],
                                ['name' => 'Domestic Express 12:00noon Parcel'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => 'dpd',
                            'displayName' => 'DPD',
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'User ID'],
                                ['name' => 'SLID'],
                                ['name' => 'Authorisation Code'],
                                ['name' => 'Start Sequence'],
                                ['name' => 'End Sequence'],
                            ],
                            'services' => [
                                ['name' => 'Express Parcel'],
                                ['name' => 'Express Document'],
                                ['name' => 'Express EU Parcel'],
                                ['name' => 'Two Day Parcel'],
                                ['name' => 'Next Day Parcel'],
                                ['name' => 'Next Day 12:00noon Parcel'],
                                ['name' => 'Next Day 10:00am Parcel'],
                                ['name' => 'Saturday Parcel'],
                                ['name' => 'Saturday 12:00noon Parcel'],
                                ['name' => 'Saturday 10:00am Parcel'],
                                ['name' => 'Classic Parcel'],
                                ['name' => 'Next day Express Pack'],
                                ['name' => 'Next day Express 12:00noon Pack'],
                                ['name' => 'Next day Express 10:00am Pack'],
                                ['name' => 'Two Day Pallet'],
                                ['name' => 'Next Day Pallet'],
                                ['name' => 'Next Day 12:00noon Pallet'],
                                ['name' => 'Next Day 10:00am Pallet'],
                                ['name' => 'Saturday Pallet'],
                                ['name' => 'Saturday 12:00noon Pallet'],
                                ['name' => 'Saturday 10:00am Pallet'],
                                ['name' => 'Classic Air'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => 'fedex',
                            'displayName' => 'FedEx',
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Key'],
                                ['name' => 'Password', 'inputType' => 'password'],
                                ['name' => 'Trans ID'],
                                ['name' => 'Meter No'],
                            ],
                            'services' => [
                                ['name' => 'International Priority Parcel'],
                                ['name' => 'International Priority Letter'],
                                ['name' => 'International Priority Pak'],
                                ['name' => 'International Economy Pak'],
                                ['name' => 'International Priority 10KG Box'],
                                ['name' => 'International Priority 25KG Box'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => 'myhermes',
                            'displayName' => 'myHermes',
                            'fields' => [
                                ['name' => 'Client ID'],
                                ['name' => 'Username'],
                                ['name' => 'Password', 'inputType' => 'password'],
                            ],
                            'services' => [
                                ['name' => 'Next Day Parcel'],
                                ['name' => 'Standard Parcel'],
                                ['name' => 'Next Day Proof Of Delivery Parcel'],
                                ['name' => 'Standard Proof Of Delivery Parcel'],
                            ],
                            'options' => [
                                'width' => false,
                                'height' => false,
                                'length' => false,
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => 'parcelforce',
                            'displayName' => 'Parcelforce',
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Contract no'],
                                ['name' => 'User ID'],
                                ['name' => 'Password', 'inputType' => 'password'],
                            ],
                            'services' => [
                                ['name' => 'Express 9 Parcel'],
                                ['name' => 'Express 10 Parcel'],
                                ['name' => 'Express AM Parcel'],
                                ['name' => 'Express 24 Parcel'],
                                ['name' => 'Express 48 Parcel'],
                                ['name' => 'Express 48 Large Parcel'],
                                ['name' => 'Global Express Parcel'],
                                ['name' => 'Europriority Home Parcel'],
                                ['name' => 'Europriority Business Parcel'],
                                ['name' => 'Global Priority Parcel'],
                                ['name' => 'Global Value Parcel'],
                                ['name' => 'Express 9 Saturday Parcel'],
                                ['name' => 'Express 10 Saturday Parcel'],
                                ['name' => 'Express AM Saturday Parcel'],
                                ['name' => 'Express 24 Saturday Parcel'],
                                ['name' => 'Express 48 Saturday Parcel'],
                                ['name' => 'Express 48 Saturday Large Parcel'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => 'royal-mail-oba',
                            'displayName' => 'Royal Mail OBA',
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'PPI Account no'],
                                ['name' => 'Wire no'],
                                ['name' => 'Contract no'],
                                ['name' => 'Hub no'],
                                ['name' => 'Posting Location no'],
                            ],
                            'services' => [
                                ['name' => 'International Business Zone Sort Priority Parcel'],
                                ['name' => 'International Business Mail Large Letter Zone Sort Priority Letter'],
                                ['name' => 'International Business Mail Letter Zone Sort Priority Letter'],
                                ['name' => 'International Business Parcel Tracked'],
                                ['name' => 'International Business Parcel Tracked Extra Compensation'],
                                ['name' => 'International Business Parcel Signed'],
                                ['name' => 'International Business Parcel Signed Extra Compensation'],
                                ['name' => 'International Business Parcel Tracked & Signed'],
                                ['name' => 'International Business Parcel Tracked & Signed Extra Compensation'],
                                ['name' => 'International Business Mail Tracked & Signed Large Letter'],
                                ['name' => 'International Business Mail Tracked & Signed Letter'],
                                ['name' => 'International Business Mail Tracked & Signed Extra Compensation Large Letter'],
                                ['name' => 'International Business Mail Tracked & Signed Extra Compensation Letter'],
                                ['name' => 'International Business Mail Tracked Large Letter'],
                                ['name' => 'International Business Mail Tracked Letter'],
                                ['name' => 'International Business Mail Tracked Extra Compensation Large Letter'],
                                ['name' => 'International Business Mail Tracked Extra Compensation Letter'],
                                ['name' => 'International Business Mail Signed Large Letter'],
                                ['name' => 'International Business Mail Signed Letter'],
                                ['name' => 'International Business Mail Signed Extra Compensation Large Letter'],
                                ['name' => 'International Business Mail Signed Extra Compensation Letter'],
                                ['name' => 'International Standard On Account Postcard'],
                                ['name' => 'International Standard On Account Parcel'],
                                ['name' => 'International Standard On Account Printed Paper'],
                                ['name' => 'International Standard On Account Letter'],
                                ['name' => 'International Standard On Account Large Letter'],
                                ['name' => 'Domestic Special Delivery Guaranteed by 1pm Letter'],
                                ['name' => 'Domestic Special Delivery Guaranteed by 1pm Large Letter'],
                                ['name' => 'Domestic Special Delivery Guaranteed by 1pm Small Parcel'],
                                ['name' => 'Domestic Special Delivery Guaranteed by 1pm Medium Parcel'],
                                ['name' => 'Domestic Special Delivery Guaranteed by 9am Letter'],
                                ['name' => 'Domestic Special Delivery Guaranteed by 9am Large Letter'],
                                ['name' => 'Domestic Special Delivery Guaranteed by 9am Small Parcel'],
                                ['name' => 'Domestic Special Delivery Guaranteed by 9am Medium Parcel'],
                                ['name' => 'Domestic RM24 Standard Letter'],
                                ['name' => 'Domestic RM24 Standard Large Letter'],
                                ['name' => 'Domestic RM24 Standard Small Parcel'],
                                ['name' => 'Domestic RM24 Standard Medium Parcel'],
                                ['name' => 'Domestic RM24 Signed For Letter'],
                                ['name' => 'Domestic RM24 Signed For Large Letter'],
                                ['name' => 'Domestic RM24 Signed For Small Parcel'],
                                ['name' => 'Domestic RM24 Signed For Medium Parcel'],
                                ['name' => 'Domestic RM48 Standard Letter'],
                                ['name' => 'Domestic RM48 Standard Large Letter'],
                                ['name' => 'Domestic RM48 Standard Small Parcel'],
                                ['name' => 'Domestic RM48 Standard Medium Parcel'],
                                ['name' => 'Domestic RM48 Signed For Letter'],
                                ['name' => 'Domestic RM48 Signed For Large Letter'],
                                ['name' => 'Domestic RM48 Signed For Small Parcel'],
                                ['name' => 'Domestic RM48 Signed For Medium Parcel'],
                                ['name' => 'Domestic 1st Class Account Mail Letter'],
                                ['name' => 'Domestic 1st Class Account Mail Large Letter'],
                                ['name' => 'Domestic 1st Class Account Mail Small Parcel'],
                                ['name' => 'Domestic 1st Class Account Mail Medium Parcel'],
                                ['name' => 'Domestic 2nd Class Account Mail Letter'],
                                ['name' => 'Domestic 2nd Class Account Mail Large Letter'],
                                ['name' => 'Domestic 2nd Class Account Mail Small Parcel'],
                                ['name' => 'Domestic 2nd Class Account Mail Medium Parcel'],
                                ['name' => 'International Signed On Account Postcard'],
                                ['name' => 'International Signed On Account Parcel'],
                                ['name' => 'International Signed On Account Printed Paper'],
                                ['name' => 'International Signed On Account Letter'],
                                ['name' => 'International Signed On Account Large Letter'],
                                ['name' => 'International Tracked On Account Postcard'],
                                ['name' => 'International Tracked On Account Parcel'],
                                ['name' => 'International Tracked On Account Printed Paper'],
                                ['name' => 'International Tracked On Account Letter'],
                                ['name' => 'International Tracked On Account Large Letter'],
                                ['name' => 'International Tracked & Signed On Account Parcel'],
                                ['name' => 'International Tracked & Signed On Account Printed Paper'],
                                ['name' => 'International Tracked & Signed On Account Letter'],
                                ['name' => 'International Tracked & Signed On Account Large Letter'],
                                ['name' => 'HM Forces International Priority Label Letter'],
                                ['name' => 'HM Forces International Priority Label Large Letter'],
                                ['name' => 'HM Forces International Priority Label Parcel'],
                                ['name' => 'HM Forces RM24 Signed For Letter'],
                                ['name' => 'HM Forces RM24 Signed For Large Letter'],
                                ['name' => 'HM Forces RM24 Signed For Parcel'],
                                ['name' => 'HM Forces Special Delivery Guaranteed By 1pm'],
                                ['name' => 'HM Forces Special Delivery Guaranteed By 1pm (£1000)'],
                                ['name' => 'HM Forces Special Delivery Guaranteed By 1pm (£2500)'],
                                ['name' => 'Royal Mail 1st Class Letter'],
                                ['name' => 'Royal Mail 2nd Class Letter'],
                                ['name' => 'Royal Mail 1st Class Large Letter'],
                                ['name' => 'Royal Mail 2nd Class Large Letter'],
                                ['name' => 'Royal Mail 1st Class Small Parcel'],
                                ['name' => 'Royal Mail 2nd Class Small Parcel'],
                                ['name' => 'Royal Mail 1st Class Medium Parcel'],
                                ['name' => 'Royal Mail 2nd Class Medium Parcel'],
                                ['name' => 'Royal Mail 1st Class Signed For Letter'],
                                ['name' => 'Royal Mail 2nd Class Signed For Letter'],
                                ['name' => 'Royal Mail 1st Class Signed For Large Letter'],
                                ['name' => 'Royal Mail 2nd Class Signed For Large Letter'],
                                ['name' => 'Royal Mail 1st Class Signed For Small Parcel'],
                                ['name' => 'Royal Mail 2nd Class Signed For Small Parcel'],
                                ['name' => 'Royal Mail 1st Class Signed For Medium Parcel'],
                                ['name' => 'Royal Mail 2nd Class Signed For Medium Parcel'],
                                ['name' => '24 Signed For Large Letter'],
                                ['name' => '24 Signed For 1st Class Parcel'],
                                ['name' => '48 Signed For Large Letter'],
                                ['name' => '48 Signed For 2nd Class Parcel'],
                                ['name' => 'International Business Parcel Zero Sort High Volume Priority'],
                                ['name' => 'International Business Parcel Zero Sort High Volume Economy'],
                                ['name' => 'International Business Parcel Zero Sort Low Volume Priority '],
                                ['name' => 'International Business Parcel Zero Sort Low Volume Economy'],
                                ['name' => 'International Business Mail Large Letter Country Sort High Volume Priority '],
                                ['name' => 'International Business Mail Large Letter Country Sort High Volume Economy '],
                                ['name' => 'International Business Mail Large Letter Country Sort Low Volume Priority '],
                                ['name' => 'International Business Mail Large Letter Country Sort Low Volume Economy '],
                                ['name' => 'International Business Parcel Zone Sort Economy '],
                                ['name' => 'International Business Mail Large Letter Zone Sort Economy'],
                                ['name' => 'International Business Mail Large Letter Zone Sort Priority Machine'],
                                ['name' => 'International Business Mail Large Letter Zone Sort Economy Machine'],
                                ['name' => 'Saturday Guaranteed by 1pm - Liability Accepted'],
                                ['name' => 'Saturday Guaranteed by 1pm - Liability Accepted (£1000)'],
                                ['name' => 'Saturday Guaranteed by 1pm - Liability Accepted (£2500)'],
                                ['name' => 'Saturday Guaranteed by 9am - Liability Accepted'],
                                ['name' => 'Saturday Guaranteed by 9am - Liability Accepted (£1000)'],
                                ['name' => 'Saturday Guaranteed by 9am - Liability Accepted (£2500)'],
                                ['name' => 'International Business Parcel Print Direct Priority Parcel'],
                                ['name' => 'International Business Parcel Print Direct Standard Parcel'],
                                ['name' => 'International Business Parcel Print Direct Economy Parcel'],
                                ['name' => 'International Business Parcel Signed Extra Compensation Country'],
                                ['name' => 'International Business Parcel Tracked Country Priced'],
                                ['name' => 'International Business Parcel Tracked Extra Compensation Country'],
                                ['name' => 'International Business Parcel Signed Country Priced'],
                                ['name' => 'International Business Parcel Tracked & Signed Country'],
                                ['name' => 'International Business Parcel Tracked & Signed Extra Compensation Country'],
                                ['name' => 'International Business Mail Tracked & Signed Country Letter'],
                                ['name' => 'International Business Mail Tracked & Signed Country Large Letter'],
                                ['name' => 'International Business Mail Tracked & Signed Extra Compensation Country Letter'],
                                ['name' => 'International Business Mail Tracked & Signed Extra Compensation Country Large Letter'],
                                ['name' => 'International Business Mail Tracked Country Priced Letter'],
                                ['name' => 'International Business Mail Tracked Country Priced Large Letter'],
                                ['name' => 'International Business Mail Tracked Extra Compensation Country Letter'],
                                ['name' => 'International Business Mail Tracked Extra Compensation Country Large Letter'],
                                ['name' => 'International Business Mail Signed Counrty Priced Letter'],
                                ['name' => 'International Business Mail Signed Counrty Priced Large Letter'],
                                ['name' => 'International Business Mail Signed Extra Compensation Country Letter'],
                                ['name' => 'International Business Mail Signed Extra Compensation Country Large Letter'],
                                ['name' => 'International Economy On Account Letter'],
                                ['name' => 'International Economy On Account Large Letter'],
                                ['name' => 'International Economy On Account Parcel'],
                                ['name' => 'International Economy On Account Printed Paper'],
                                ['name' => 'International Signed On Account Extra Compensation Letter'],
                                ['name' => 'International Signed On Account Extra Compensation Large Letter'],
                                ['name' => 'International Signed On Account Extra Compensation Parcel'],
                                ['name' => 'International Signed On Account Extra Compensation Printed Paper'],
                                ['name' => 'International Tracked On Account Extra Compensation Letter'],
                                ['name' => 'International Tracked On Account Extra Compensation Large Letter'],
                                ['name' => 'International Tracked On Account Extra Compensation Parcel'],
                                ['name' => 'International Tracked On Account Extra Compensation Printed Paper'],
                                ['name' => 'International Tracked & Signed On Account Extra Compensation Letter'],
                                ['name' => 'International Tracked & Signed On Account Extra Compensation Large Letter'],
                                ['name' => 'International Tracked & Signed On Account Extra Compensation Parcel'],
                                ['name' => 'International Tracked & Signed On Account Extra Compensation Printed Paper'],
                                ['name' => 'International Business Mail Mixed Zone Sort Priority Letter'],
                                ['name' => 'International Business Mail Mixed Zone Sort Economy Letter'],
                                ['name' => 'International Business Mail Mixed Zone Sort Priority Machine Letter'],
                                ['name' => 'International Business Mail Mixed Zone Sort Economy Machine Letter'],
                                ['name' => 'International Business Parcel Max Sort Economy'],
                                ['name' => 'International Business Mail Large Letter Max Sort Priority '],
                                ['name' => 'International Business Mail Large Letter Max Sort Economy'],
                                ['name' => 'International Business Parcel Max Sort Priority'],
                                ['name' => 'International Business Mail Large Letter Max Sort Standard '],
                                ['name' => 'International Business Parcel Max Sort Standard '],
                                ['name' => 'Saturday Guaranteed by 1pm (£1000)'],
                                ['name' => 'Saturday Guaranteed by 1pm (£2500)'],
                                ['name' => 'Saturday Guaranteed by 9am (£1000)'],
                                ['name' => 'Saturday Guaranteed by 9am (£2500)'],
                                ['name' => 'Royal Mail Tracked 24 Parcel'],
                                ['name' => 'Royal Mail Tracked 24 Signed For Parcel'],
                                ['name' => 'Royal Mail Tracked 48 Parcel'],
                                ['name' => 'Royal Mail Tracked 48 Signed For Parcel'],
                                ['name' => 'Tracked Returns 24 Parcel'],
                                ['name' => 'Tracked Returns 48 Parcel'],
                                ['name' => 'International Business Parcel Zero Sort Priority'],
                                ['name' => 'International Business Parcel Zero Sort Economy'],
                                ['name' => 'International Business Mail Large Letter Zero Sort Priority '],
                                ['name' => 'International Business Mail Large Letter Zero Sort Economy '],
                                ['name' => 'International Business Mail Large Letter Zero Sort Priority Machine'],
                                ['name' => 'International Business Mail Large Letter Zero Sort Economy Machine'],
                                ['name' => 'International Business Mail Mixed Zero Sort Priority Letter'],
                                ['name' => 'International Business Mail Mixed Zero Sort Economy Letter'],
                                ['name' => 'International Business Mail Mixed Zero Sort Priority Machine'],
                                ['name' => 'International Business Mail Mixed Zero Sort Economy Machine'],
                                ['name' => 'Royal Mail 24 Sort 8 Large Letter Flat Rate'],
                                ['name' => 'Royal Mail 48 Sort 8 Large Letter Flat Rate'],
                                ['name' => 'Royal Mail 24 Presorted Large Letter'],
                                ['name' => 'Royal Mail 48 Presorted Large Letter'],
                                ['name' => 'Royal Mail 48 Large Letter Flat Rate '],
                                ['name' => 'Royal Mail 24 Sort 8 Letter Flat Rate'],
                                ['name' => 'Royal Mail 48 Sort 8 Letter Flat Rate'],
                                ['name' => 'Royal Mail 24 Sort 8 Large Letter Daily Rate '],
                                ['name' => 'Royal Mail 24 Sort 8 Letter Daily Rate '],
                                ['name' => 'Royal Mail 48 Sort 8 Large Letter Daily Rate '],
                                ['name' => 'Royal Mail 48 Sort 8 Letter Daily Rate '],
                                ['name' => 'Royal Mail 24 Presorted Letter'],
                                ['name' => 'Royal Mail 48 Presorted Letter'],
                                ['name' => 'Royal Mail 24 Large Letter Flat Rate'],
                                ['name' => 'RM24 Presorted Letter Annual Flat Rate'],
                                ['name' => 'RM48 Presorted Letter Annual Flat Rate'],
                                ['name' => 'RM24 Presorted Large Letter Annual Flat Rate'],
                                ['name' => 'RM48 Presorted Large Letter Annual Flat Rate'],
                                ['name' => 'Parcel Post Flat Rate Annual'],
                                ['name' => 'RM24 Large Letter Annual Flat Rate'],
                                ['name' => 'RM48 Large Letter Annual Flat Rate'],
                                ['name' => 'RM 48 Sort 8 Letter Annual Flat Rate'],
                                ['name' => 'Royal Mail 24 Large Letter Daily Rate'],
                                ['name' => 'Royal Mail 24 Letter Daily Rate'],
                                ['name' => 'Royal Mail 48 Large Letter Daily Rate'],
                                ['name' => 'Royal Mail 48 Letter Daily Rate'],
                                ['name' => 'Royal Mail 24 Letter Flat Rate'],
                                ['name' => 'Royal Mail 48 Letter Flat Rate'],
                                ['name' => 'RM 24 Sort 8 Large Letter Annual Flat Rate'],
                                ['name' => 'RM 24 Sort 8 Letter Annual Flat Rate'],
                                ['name' => 'RM 24 Sort 8 Large Letter Annual Flat Rate'],
                                ['name' => 'RM 24 Sort 8 Letter Annual Flat Rate'],
                                ['name' => 'RM 48 Sort 8 Large Letter Annual Flat Rate'],
                                ['name' => 'Royal Mail Tracked 48 High Volume Parcel'],
                                ['name' => 'Royal Mail Tracked 24 High Volume Parcel'],
                                ['name' => 'Royal Mail Tracked 48 Letterboxable High Volume Parcel'],
                                ['name' => 'Royal Mail Tracked 24 Letterboxable High Volume Parcel'],
                                ['name' => 'Royal Mail Tracked 24 Letterboxable Parcel'],
                                ['name' => 'Royal Mail Tracked 48 Letterboxable Parcel'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => 'tnt',
                            'displayName' => 'TNT',
                            'fields' => [
                                ['name' => 'ExpressConnect Account no'],
                                ['name' => 'ExpressConnect Customer ID'],
                                ['name' => 'ExpressConnect Password', 'inputType' => 'password'],
                                ['name' => 'ExpressLabel User'],
                                ['name' => 'ExpressLabel Password', 'inputType' => 'password'],
                            ],
                            'services' => [
                                ['name' => 'Express Parcel'],
                                ['name' => 'Express Docs'],
                                ['name' => 'Economy Express Parcel'],
                                ['name' => 'Next Day 09:00am Parcel'],
                                ['name' => 'Next Day 10:00am Parcel'],
                                ['name' => 'Next Day 12:00noon Parcel'],
                                ['name' => 'Next Day 17:00pm Parcel'],
                                ['name' => 'Saturday Parcel'],
                                ['name' => 'Saturday 09:00am Parcel'],
                                ['name' => 'Saturday 10:00am Parcel'],
                                ['name' => 'Saturday 12:00noon Parcel'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => 'uk-mail',
                            'displayName' => 'UK Mail',
                            'fields' => [
                                ['name' => 'Account no (Item Rates)'],
                                ['name' => 'Account no (KG Rates)'],
                                ['name' => 'User ID'],
                            ],
                            'services' => [
                                ['name' => 'Parcel Next Day'],
                                ['name' => 'Parcel Next Day by 12:00noon'],
                                ['name' => 'Parcel Next Day by 10:30am'],
                                ['name' => 'Parcel Next day by 09:00am'],
                                ['name' => 'Parcel Saturday'],
                                ['name' => 'Parcel Saturday by 10:30am'],
                                ['name' => 'Parcel Saturday by 09:00am'],
                                ['name' => 'Parcel 48 Hr'],
                                ['name' => 'Parcel 72 Hour'],
                                ['name' => 'Small Bag it Next Day'],
                                ['name' => 'Small Bag it Next Day by 12:00noon'],
                                ['name' => 'Small Bag it Next Day by 10:30am'],
                                ['name' => 'Small Bag it Next day by 09:00am'],
                                ['name' => 'Small Bag it Saturday'],
                                ['name' => 'Medium Bag it Next Day by 12:00noon'],
                                ['name' => 'Medium Bag it Next Day by 10:30am'],
                                ['name' => 'Medium Bag it Next day by 09:00am'],
                                ['name' => 'Medium Bag it Saturday'],
                                ['name' => 'Large Bag it Next Day'],
                                ['name' => 'Large Bag it Next Day by 12:00noon'],
                                ['name' => 'Large Bag it Next Day by 10:30am'],
                                ['name' => 'Large Bag it Next day by 09:00am'],
                                ['name' => 'Large Bag it Saturday'],
                                ['name' => 'Extra Large Bag it Next Day'],
                                ['name' => 'Extra Large Bag it Next Day by 12:00noon'],
                                ['name' => 'Extra Large Bag it Next Day by 10:30am'],
                                ['name' => 'Extra Large Bag it Next day by 09:00am'],
                                ['name' => 'Extra Large Bag it Saturday'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
                            ],
                        ],
                        [
                            'channelName' => 'ups',
                            'displayName' => 'UPS',
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Book no'],
                                ['name' => 'Username'],
                                ['name' => 'Prefix'],
                                ['name' => 'Start Sequence'],
                                ['name' => 'End Sequence'],
                            ],
                            'services' => [
                                ['name' => 'Express Parcel'],
                                ['name' => 'Express Letter'],
                                ['name' => 'Express Pak'],
                                ['name' => 'Express Saver Parcel'],
                                ['name' => 'Express Saver Letter'],
                                ['name' => 'Express Saver Pak'],
                                ['name' => 'Standard Single Parcel'],
                                ['name' => 'Standard Multi Parcel'],
                                ['name' => 'Expedited Parcel'],
                                ['name' => 'Express Plus Parcel'],
                                ['name' => 'Express Plus Letter'],
                                ['name' => 'Express Plus Pak'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                            ],
                        ],
                        [
                            'channelName' => 'yodel',
                            'displayName' => 'Yodel',
                            'fields' => [
                                ['name' => 'Account no'],
                                ['name' => 'Meter no'],
                                ['name' => 'Contract no'],
                                ['name' => 'Schedule no'],
                                ['name' => 'Username'],
                                ['name' => 'Licence Plate Prefix'],
                                ['name' => 'Start Sequence'],
                                ['name' => 'End Sequence'],
                            ],
                            'services' => [
                                ['name' => 'Priority 12:00noon Parcel'],
                                ['name' => 'Express 24 Parcel'],
                                ['name' => 'Express 48 Parcel'],
                                ['name' => 'Saturday Priority 12:00noon Parcel'],
                                ['name' => 'Express 24 (NI) Parcel'],
                                ['name' => 'Express (Isle) Parcel'],
                                ['name' => 'Express 24 Return Parcel'],
                                ['name' => 'Priority 10:00am (NI) Parcel'],
                                ['name' => 'Priority 10:00am Parcel'],
                                ['name' => 'Saturday 10:00am Parcel'],
                                ['name' => 'Express 24 (NI INT) Parcel'],
                                ['name' => 'Express 24 (UK) Parcel'],
                                ['name' => 'Express 48 (UK) Parcel'],
                                ['name' => '@Home 24 (NI) Parcel'],
                                ['name' => '@Home 48 (NI) Parcel'],
                                ['name' => '@Home 24 (BT) Parcel'],
                                ['name' => '@Home 24 Catalogue Parcel'],
                                ['name' => '@Home 72 Parcel '],
                                ['name' => '@Home 72 (NI) Parcel'],
                                ['name' => 'Express 48 (NI) Parcel'],
                                ['name' => '@Home 24 Parcel'],
                                ['name' => '@Home 48 Parcel'],
                                ['name' => '@Home Return Parcel'],
                                ['name' => 'GRN Next Day Parcel'],
                                ['name' => 'STEC Parcel'],
                                ['name' => 'SRTN Parcel'],
                                ['name' => 'Express Saturday Parcel'],
                                ['name' => '@Home Saturday Parcel'],
                                ['name' => '@Home Pack Parcel'],
                            ],
                            'options' => [
                                'insuranceMonetary' => false,
                                'signature' => false,
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
