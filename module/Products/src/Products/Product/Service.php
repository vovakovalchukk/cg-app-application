<?php
namespace Products\Product;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Channel\Type as ChannelType;
use CG\ETag\Exception\NotModified;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\Http\Exception\Exception3xx\NotModified as HttpNotModified;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Locale\Length as LocaleLength;
use CG\Locale\Mass as LocaleMass;
use CG\Locale\VATRelevant;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Client\Service as ProductService;
use CG\Product\Detail\Client\Service as DetailService;
use CG\Product\Detail\Entity as Details;
use CG\Product\Detail\Mapper as DetailMapper;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\Product\Exception\ProductLinkBlockingProductDeletionException;
use CG\Product\Filter as ProductFilter;
use CG\Product\Filter\Mapper as ProductFilterMapper;
use CG\Product\Gearman\Workload\Remove as ProductRemoveWorkload;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\LinkNode\Filter as ProductLinkNodeFilter;
use CG\Product\LinkNode\Service as ProductLinkNodeService;
use CG\Product\Remove\ProgressStorage as RemoveProgressStorage;
use CG\Product\StockMode;
use CG\Settings\Product\Entity as ProductSettings;
use CG\Settings\Product\Service as ProductSettingsService;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\ValidationException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Adjustment\Service as StockAdjustmentService;
use CG\Stock\Auditor as StockAuditor;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as Stock;
use CG\Stock\Filter;
use CG\Stock\Location\StorageInterface as StockLocationStorage;
use CG\Stock\StorageInterface as StockStorage;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\User\OrganisationUnit\Service as UserOuService;
use CG\User\Service as UserService;
use CG\UserPreference\Client\Service as UserPreferenceService;
use GearmanClient;
use Products\Product\Details\ChannelInterface as ChannelDetails;
use Zend\Di\Di;
use Zend\Navigation\Page\AbstractPage as NavPage;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    const PRODUCT_TABLE_COL_PREF_KEY = 'product-columns';
    const PRODUCT_TABLE_COL_POS_PREF_KEY = 'product-column-positions';
    const PRODUCT_SIDEBAR_STATE_KEY = 'product-sidebar-state';
    const PRODUCT_FILTER_BAR_STATE_KEY = 'product-filter-bar-state';
    const ACCOUNTS_PAGE = 1;
    const ACCOUNTS_LIMIT = 'all';
    const LIMIT = 50;
    const PAGE = 1;
    const MAX_FOREGROUND_DELETES = 5;
    const MAX_SAVE_ATTEMPTS = 2;
    const NAV_KEY_FEATURE_FLAG = 'featureFlag';
    const LOG_CODE = 'ProductProductService';
    const LOG_NO_STOCK_TO_DELETE = 'No stock found to remove for Product %s when deleting it';
    const STAT_STOCK_UPDATE_MANUAL = 'stock.update.manual.%d.%d';
    const EVENT_MANUAL_STOCK_CHANGE = 'Manual Stock Change';
    const LOG_PRODUCT_NOT_FOUND = 'Tried saving product %s with taxRateId %s but the product could not be found';
    const LOG_PRODUCT_NAME_ERROR = 'Tried saving product %s with name "%s" but an error occurred';
    const DETAILTYPES_WITH_UNITS_OF_MEASURE = [
        'weight' => 'weight',
        'length' => 'length',
        'width' => 'width',
        'height' => 'height',
    ];

    /** @var UserService $userService */
    protected $userService;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var Di $di */
    protected $di;
    /** @var $activeUserPreference */
    protected $activeUserPreference;
    /** @var UserPreferenceService $userPreferenceService */
    protected $userPreferenceService;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;
    /** @var ProductService $productService */
    protected $productService;
    /** @var ProductFilterMapper $productFilterMapper */
    protected $productFilterMapper;
    /** @var StockStorage $stockStorage */
    protected $stockStorage;
    /** @var StockLocationStorage $stockLocationStorage */
    protected $stockLocationStorage;
    /** @var StockAuditor $stockAuditor */
    protected $stockAuditor;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var StockAdjustmentService $stockAdjustmentService */
    protected $stockAdjustmentService;
    /** @var DetailService $detailService */
    protected $detailService;
    /** @var DetailMapper $detailMapper */
    protected $detailMapper;
    /** @var GearmanClient $gearmanClient */
    protected $gearmanClient;
    /** @var RemoveProgressStorage $removeProgressStorage */
    protected $removeProgressStorage;
    /** @var  FeatureFlagsService $featureFlagsService */
    protected $featureFlagsService;
    /** @var UserOuService $userOuService */
    protected $userOuService;
    /** @var ProductLinkNodeService $productLinkNodeService */
    protected $productLinkNodeService;
    /** @var ProductSettingsService */
    protected $productSettingsService;
    /** @var ChannelDetails[] */
    protected $channelDetails;

    public function __construct(
        UserService $userService,
        ActiveUserInterface $activeUserContainer,
        Di $di,
        UserPreferenceService $userPreferenceService,
        AccountService $accountService,
        OrganisationUnitService $organisationUnitService,
        ProductFilterMapper $productFilterMapper,
        ProductService $productService,
        StockLocationStorage $stockLocationStorage,
        StockStorage $stockStorage,
        StockAuditor $stockAuditor,
        IntercomEventService $intercomEventService,
        StockAdjustmentService $stockAdjustmentService,
        DetailService $detailService,
        DetailMapper $detailMapper,
        GearmanClient $gearmanClient,
        RemoveProgressStorage $removeProgressStorage,
        FeatureFlagsService $featureFlagsService,
        UserOuService $userOuService,
        ProductLinkNodeService $productLinkNodeService,
        ProductSettingsService $productSettingsService
    ) {
        $this->productService = $productService;
        $this->userService = $userService;
        $this->productFilterMapper = $productFilterMapper;
        $this->activeUserContainer = $activeUserContainer;
        $this->di = $di;
        $this->userPreferenceService = $userPreferenceService;
        $this->accountService = $accountService;
        $this->organisationUnitService = $organisationUnitService;
        $this->stockStorage = $stockStorage;
        $this->stockLocationStorage = $stockLocationStorage;
        $this->stockAuditor = $stockAuditor;
        $this->intercomEventService = $intercomEventService;
        $this->stockAdjustmentService = $stockAdjustmentService;
        $this->detailService = $detailService;
        $this->detailMapper = $detailMapper;
        $this->gearmanClient = $gearmanClient;
        $this->removeProgressStorage = $removeProgressStorage;
        $this->featureFlagsService = $featureFlagsService;
        $this->userOuService = $userOuService;
        $this->productLinkNodeService = $productLinkNodeService;
        $this->productSettingsService = $productSettingsService;
        $this->channelDetails = [];
    }

    /**
     * @return self
     */
    public function registerChannelDetail(string $channel, ChannelDetails $details)
    {
        $this->channelDetails[$channel] = $details;
        return $this;
    }

    public function fetchProducts(ProductFilter $productFilter, $limit = self::LIMIT, $page = self::PAGE)
    {
        $productFilter
            ->setLimit($limit)
            ->setPage($page)
            ->setOrganisationUnitId($this->activeUserContainer->getActiveUser()->getOuList());

        try {
            $collection = new ProductCollection(Product::class, __FUNCTION__);
            return $this->productService->fetchCollectionPaginated($productFilter, $collection);
        } catch (\InvalidArgumentException $exception) {
            return $this->productService->fetchCollectionByFilter($productFilter);
        }
    }

    public function fetchProductById($id): Product
    {
        return $this->productService->fetch($id);
    }

    public function fetchStockForSku(string $productSku, int $ouId): array
    {
        if ($productSku == '') {
            throw new ValidationException('Cannot filter stock by empty sku');
        }

        $linkNodeFilter = (new ProductLinkNodeFilter('all', 1))
            ->setOuIdProductSku([ProductLink::generateId($ouId, $productSku)]);
        try {
            $linkNode = $this->productLinkNodeService->fetchCollectionByFilter($linkNodeFilter)->getFirst();
            $ancestors = $linkNode->getAncestors();
        } catch (NotFound $exception) {
            $ancestors = [];
        }
        $stockCollection = $this->fetchStockCollectionBySkusAndOuId(array_merge([$productSku], $ancestors), $ouId);

        $stockBySku = [];
        /** @var Stock $stock */
        foreach ($stockCollection as $stock) {
            $stockBySku[$stock->getSku()] = array_merge(
                $stock->toArray(),
                ['locations' => $stock->getLocations()->toArray()]
            );
        }

        return $stockBySku;
    }

    protected function fetchStockCollectionBySkusAndOuId(array $skus, int $ouId): StockCollection
    {
        $stockFilter = (new Filter('all', 1))
            ->setSku($skus)
            ->setOrganisationUnitId([$ouId]);
        return $this->stockStorage->fetchCollectionByFilter($stockFilter);
    }

    public function fetchProductDetails(Product $product, string $locale, array $channelDetails = []): array
    {
        $detailsEntity = $product->getDetails();
        if (!$detailsEntity) {
            return [
                'sku' => $product->getSku()
            ];
        }

        return array_merge([
            'id' => $detailsEntity->getId(),
            'sku' => $detailsEntity->getSku(),
            'weight' => $detailsEntity->getDisplayWeight($locale),
            'width' => $detailsEntity->getDisplayWidth($locale),
            'height' => $detailsEntity->getDisplayHeight($locale),
            'length' => $detailsEntity->getDisplayLength($locale),
            'price' => $detailsEntity->getPrice(),
            'description' => $detailsEntity->getDescription(),
            'condition' => $detailsEntity->getCondition(),
            'brand' => $detailsEntity->getBrand(),
            'mpn' => $detailsEntity->getMpn(),
            'ean' => $detailsEntity->getEan(),
            'upc' => $detailsEntity->getUpc(),
            'isbn' => $detailsEntity->getIsbn(),
            'barcodeNotApplicable' => $detailsEntity->isBarcodeNotApplicable(),
            'cost' => $detailsEntity->getDisplayCost(),
            'supplierId' => $detailsEntity->getSupplierId(),
            'hsTariffNumber' => $detailsEntity->getHsTariffNumber(),
            'countryOfManufacture' => $detailsEntity->getCountryOfManufacture()
        ], $channelDetails);
    }

    public function fetchChannelDetails(array $productIds, array $accounts): array
    {
        $channelDetails = [];
        foreach ($this->channelDetails as $channel => $details) {
            $channelDetails += $this->channelDetails[$channel]->fetchChannelDetails(
                $productIds,
                array_keys(array_filter($accounts, function(array $account) use($channel) {
                    return $account['channel'] === $channel;
                }))
            );
        }
        return $channelDetails;
    }

    public function updateStock($stockLocationId, $eTag, $totalQuantity)
    {
        try {
            $stockLocationEntity = $this->stockLocationStorage->fetch($stockLocationId);

            $adjustment = $this->createAndAuditStockAdjustment($stockLocationEntity, $totalQuantity);
            $stockLocationEntity->setStoredEtag($eTag);
            $this->stockAdjustmentService->applyAdjustmentAndSave($adjustment, $stockLocationEntity);
            $this->statsIncrement(
                static::STAT_STOCK_UPDATE_MANUAL, [
                    $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                    $this->activeUserContainer->getActiveUser()->getId()
                ]
            );
            $this->notifyOfStockUpdate();
        } catch (NotModified $e) {
            //No changes do nothing
        }
        return $stockLocationEntity;
    }

    protected function notifyOfStockUpdate()
    {
        $event = new IntercomEvent(static::EVENT_MANUAL_STOCK_CHANGE, $this->getActiveUser()->getId());
        $this->intercomEventService->save($event);
    }

    public function deleteProductsById(array $productIds, $progressKey)
    {
        if (count($productIds) <= static::MAX_FOREGROUND_DELETES) {
            $filter = new ProductFilter(static::ACCOUNTS_LIMIT, static::PAGE, [], null, [], $productIds);
            $products = $this->productService->fetchCollectionByFilter($filter);
            /** @var Product $product */
            foreach ($products as $product) {
                $this->productService->hardRemove($product);
            }

            $this->removeProgressStorage->setProgress($progressKey, count($productIds));
            return;
        }
        // Deleting lots of products is resource intensive, background it
        foreach ($productIds as $productId) {
            $workload = new ProductRemoveWorkload($productId, $progressKey);
            $handle = ProductRemoveWorkload::FUNCTION_NAME . '-' . $productId;
            $this->gearmanClient->doBackground(ProductRemoveWorkload::FUNCTION_NAME, serialize($workload), $handle);
        }
    }

    /**
     * @param array $productIds
     * @throws ProductLinkBlockingProductDeletionException
     */
    public function checkForSafeDeletionWithProductLinks(array $productIds = [])
    {
        $filter = new ProductFilter('all', 1, [], null, [], $productIds);
        $ouIdSkuListOfProductsAndVariations = $this->productService->getSkusOfProductsAndVariationsWithoutDuplicateSku(
            $this->productService->fetchCollectionByFilter($filter)
        );
        if (!empty($ouIdSkuListOfProductsAndVariations)) {
            $this->productLinkNodeService->getLinkSkusForDeletion($ouIdSkuListOfProductsAndVariations);
        }
    }

    public function checkProgressOfDeleteProducts($progressKey)
    {
        return (int)$this->removeProgressStorage->getProgress($progressKey);
    }

    public function saveProductTaxRateId($productId, $taxRateId, $memberState)
    {
        try {
            $product = $this->productService->fetch($productId);

            $oldTaxRates = $product->getTaxRateIds();
            $newTaxRates = array_merge($oldTaxRates, [$memberState => $taxRateId]);

            $this->productService->save($product->setTaxRateIds($newTaxRates));
        } catch (NotFound $e) {
            $this->logWarning(static::LOG_PRODUCT_NOT_FOUND, [$productId, $taxRateId], static::LOG_CODE);
        }  catch (HttpNotModified $e) {
            // Do nothing
        }
    }

    public function saveProductPickLocations($productId, array $pickLocations)
    {
        /** @var Product $product */
        $product = $this->productService->fetch($productId);
        try {
            $this->productService->save(
                $product->setPickingLocations($pickLocations)
            );
        } catch (NotModified | HttpNotModified $exception) {
            // Ignore - product is up to date
        }
    }

    public function saveProductName($productId, $newName)
    {
        try {
            $product = $this->productService->fetch($productId);

            $this->productService->save($product->setName($newName));
        } catch (NotFound $e) {
            $this->logWarning(static::LOG_PRODUCT_NAME_ERROR, [$productId, $newName], static::LOG_CODE);
        }
    }

    public function isSidebarVisible()
    {
        $preference = $this->getActiveUserPreference()->getPreference();
        $visible = isset($preference[static::PRODUCT_SIDEBAR_STATE_KEY]) ? $preference[static::PRODUCT_SIDEBAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    public function isFilterBarVisible()
    {
        $preference = $this->getActiveUserPreference()->getPreference();
        $visible = isset($preference[static::PRODUCT_FILTER_BAR_STATE_KEY]) ? $preference[static::PRODUCT_FILTER_BAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    protected function fetchUserPrefItem($key)
    {
        $userPrefsPref = $this->getActiveUserPreference()->getPreference();
        $storedItem = (isset($userPrefsPref[$key]) ? $userPrefsPref[$key] : []);
        return $storedItem;
    }

    protected function saveUserPrefItem($key, $value)
    {
        $userPrefs = $this->getActiveUserPreference();
        $userPrefsPref = $userPrefs->getPreference();
        $userPrefsPref[$key] = $value;
        $userPrefs->setPreference($userPrefsPref);

        $this->userPreferenceService->save($userPrefs);
    }

    protected function createAndAuditStockAdjustment($stockLocationEntity, $newTotal)
    {
        $oldTotal = $stockLocationEntity->getOnHand();
        if ($newTotal > $oldTotal) {
            $diff = $newTotal - $oldTotal;
            $operator = StockAdjustment::OPERATOR_INC;
        } else {
            $diff = $oldTotal - $newTotal;
            $operator = StockAdjustment::OPERATOR_DEC;
        }
        $adjustment = $this->stockAdjustmentService->createAdjustment(StockAdjustment::TYPE_ONHAND, $diff, $operator);
        $stock = $this->stockStorage->fetch($stockLocationEntity->getStockId());
        $this->stockAuditor->userAdjustment(
            $adjustment,
            $stock->getSku(),
            $stock->getOrganisationUnitId()
        );
        return $adjustment;
    }

    public function getAccountStockSettingsEnabledStatus()
    {
        $statuses = [StockMode::LIST_ALL => true, StockMode::LIST_FIXED => false, StockMode::LIST_MAX => false];
        try {
            $accounts = $this->getSalesAccounts();
            foreach ($accounts as $account) {

                if ($account->getStockFixedEnabled()) {
                    $statuses[StockMode::LIST_FIXED] = true;
                }
                if ($account->getStockMaximumEnabled()) {
                    $statuses[StockMode::LIST_MAX] = true;
                }
                if ($statuses[StockMode::LIST_FIXED] == true && $statuses[StockMode::LIST_MAX] == true) {
                    break;
                }
            }
        } catch (NotFound $e) {
            // No-op
        }
        return $statuses;
    }

    protected function getSalesAccounts()
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setType(ChannelType::SALES)
            ->setOrganisationUnitId([$rootOuId])
            ->setDeleted(false);
        return $this->accountService->fetchByFilter($filter);
    }

    public function saveProductDetail(string $sku, string $detail, $value, int $id = null): int
    {
        return $this->saveProductDetails($sku, [$detail => $value], $id);
    }

    public function saveProductDetails(string $sku, array $details, int $id = null): int
    {
        array_walk($details, function(&$value, string $detail) {
            if ($this->doesDetailTypeHaveUOM($detail)) {
                $value = $this->convertDetailValueToInternalUnitOfMeasurement($detail, $value);
            }
        });

        if ($id) {
            $this->detailService->patchEntity($id, $details);
        } else {
            $id = $this->detailService->save(
                $this->detailMapper->fromArray(array_merge(
                    $details,
                    [
                        'organisationUnitId' => $this->getActiveUserRootOu(),
                        'sku' => $sku,
                    ]
                ))
            )->getId();
        }

        return $id;
    }

    public function saveProductChannelDetail(int $productId, string $channel, string $detail, $value, int $accountId = null): void
    {
        $this->saveProductChannelDetails($productId, $channel, [$detail => $value], $accountId);
    }

    public function saveProductChannelDetails(int $productId, string $channel, array $details, int $accountId = null): void
    {
        if (!isset($this->channelDetails[$channel])) {
            throw new \RuntimeException(sprintf('No %s channel details service registered', $channel));
        }

        $this->channelDetails[$channel]->saveDetails($productId,  $details, $accountId);
    }

    public function saveStockIncludePurchaseOrdersForProduct(Product $product, ?bool $includePurchaseOrders): Stock
    {
        $stockCollection = $this->fetchStockCollectionBySkusAndOuId([$product->getSku()],
            $product->getOrganisationUnitId());
        /** @var Stock $stock */
        $stock = $stockCollection->getFirst();
        return $this->saveStockIncludePurchaseOrders($stock, $includePurchaseOrders);
    }

    protected function saveStockIncludePurchaseOrders(Stock $stock, ?bool $includePurchaseOrders): Stock
    {
        /** @var ProductSettings $productSettings */
        $productSettings = $this->productSettingsService->fetch($stock->getOrganisationUnitId());
        for ($attempt = 1; $attempt <= static::MAX_SAVE_ATTEMPTS; $attempt++) {
            if ($includePurchaseOrders === null) {
                $stock->setIncludePurchaseOrdersUseDefault(true)
                    ->setIncludePurchaseOrders($productSettings->isIncludePurchaseOrdersInAvailable());
            } else {
                $stock->setIncludePurchaseOrdersUseDefault(false)
                    ->setIncludePurchaseOrders($includePurchaseOrders);
            }
            try {
                return $this->stockStorage->save($stock);
            } catch (HttpNotModified $e) {
                // No-op
                return $stock;
            } catch (Conflict $e) {
                if ($attempt >= static::MAX_SAVE_ATTEMPTS) {
                    throw $e;
                }
                $stock = $this->stockStorage->fetch($stock->getId());
            }
        }
    }

    protected function getActiveUserPreference()
    {
        if (!isset($this->activeUserPreference)) {
            $activeUserId = $this->getActiveUser()->getId();
            $this->activeUserPreference = $this->userPreferenceService->fetch($activeUserId);
        }

        return $this->activeUserPreference;
    }

    /**
     * @return User
     */
    protected function getActiveUser()
    {
        return $this->activeUserContainer->getActiveUser();
    }

    protected function getActiveUserRootOu()
    {
        return $this->organisationUnitService->getRootOuIdFromOuId(
            $this->getActiveUser()->getOrganisationUnitId()
        );
    }

    public function checkPageEnabled(NavPage $page)
    {
        if (!$featureFlag = $page->get(static::NAV_KEY_FEATURE_FLAG)) {
            return;
        }

        try {
            if (!($this->userOuService->getActiveUser() instanceof User)) {
                throw new NotFound("User is not logged in.");
            }
            $ou = $this->userOuService->getRootOuByActiveUser();
            if (!$this->featureFlagsService->isActive($featureFlag, $ou)) {
                $page->setClass('disabled');
            }
        } catch (\Exception $e) {
            // No-op, don't stop rendering the nav just for this
        }
    }

    public function isVatRelevant(): bool
    {
        if (VATRelevant::getForLocale($this->activeUserContainer->getLocale())) {
            return true;
        }
        $ou = $this->userOuService->getRootOuByActiveUser();
        return $ou->isVatRegistered();
    }

    protected function doesDetailTypeHaveUOM(string $detailType)
    {
        return isset(static::DETAILTYPES_WITH_UNITS_OF_MEASURE[strtolower($detailType)]);
    }

    protected function convertDetailValueToInternalUnitOfMeasurement(string $detailType, $value): float
    {
        $value = is_numeric($value) ? (float) $value : null;
        $locale = $this->activeUserContainer->getLocale();
        if ($detailType == 'weight') {
            $displayUnit = LocaleMass::getForLocale($locale);
            $value = Details::convertMass($value, $displayUnit, Details::UNIT_MASS);
        } else {
            $displayUnit = LocaleLength::getForLocale($locale);
            $value = Details::convertLength($value, $displayUnit, Details::UNIT_LENGTH);
        }
        return $value;
    }
}
