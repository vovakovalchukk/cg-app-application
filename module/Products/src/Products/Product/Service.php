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
use CG\Product\Entity as Product;
use CG\Product\Exception\ProductLinkBlockingProductDeletionException;
use CG\Product\LinkNode\Service as ProductLinkNodeService;
use CG\Product\LinkNode\Filter as ProductLinkNodeFilter;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Filter as ProductFilter;
use CG\Product\Filter\Mapper as ProductFilterMapper;
use CG\Product\Gearman\Workload\Remove as ProductRemoveWorkload;
use CG\Product\Remove\ProgressStorage as RemoveProgressStorage;
use CG\Product\StockMode;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\ValidationException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Adjustment\Service as StockAdjustmentService;
use CG\Stock\Auditor as StockAuditor;
use CG\Stock\Filter;
use CG\Stock\Entity as Stock;
use CG\Stock\Location\StorageInterface as StockLocationStorage;
use CG\Stock\StorageInterface as StockStorage;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\User\OrganisationUnit\Service as UserOuService;
use CG\User\Service as UserService;
use CG\UserPreference\Client\Service as UserPreferenceService;
use GearmanClient;
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
    const NAV_KEY_FEATURE_FLAG = 'featureFlag';
    const LOG_CODE = 'ProductProductService';
    const LOG_NO_STOCK_TO_DELETE = 'No stock found to remove for Product %s when deleting it';
    const STAT_STOCK_UPDATE_MANUAL = 'stock.update.manual.%d.%d';
    const EVENT_MANUAL_STOCK_CHANGE = 'Manual Stock Change';
    const LOG_PRODUCT_NOT_FOUND = 'Tried saving product %s with taxRateId %s but the product could not be found';
    const LOG_PRODUCT_NAME_ERROR = 'Tried saving product %s with name "%s" but an error occurred';

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
        ProductLinkNodeService $productLinkNodeService
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
    }

    public function fetchProducts(ProductFilter $productFilter, $limit = self::LIMIT, $page = self::PAGE)
    {
        $productFilter
            ->setLimit($limit)
            ->setPage($page)
            ->setOrganisationUnitId($this->activeUserContainer->getActiveUser()->getOuList());
        $products = $this->productService->fetchCollectionByFilter($productFilter);
        return $products;
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
        $stockFilter = (new Filter('all', 1))
            ->setSku(array_merge([$productSku], $ancestors))
            ->setOrganisationUnitId([$ouId]);
        $stockCollection = $this->stockStorage->fetchCollectionByFilter($stockFilter);

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

    public function saveProductDetail($sku, $detail, $value, $id = null)
    {
        $value = is_numeric($value) ? (float) $value : null;
        $locale = $this->activeUserContainer->getLocale();
        if ($detail == 'weight') {
            $displayUnit = LocaleMass::getForLocale($locale);
            $value = Details::convertMass($value, $displayUnit, Details::UNIT_MASS);
        } else {
            $displayUnit = LocaleLength::getForLocale($locale);
            $value = Details::convertLength($value, $displayUnit, Details::UNIT_LENGTH);
        }

        if ($id) {
            $this->detailService->patchEntity($id, [$detail => $value]);
        } else {
            /** @var Details $details */
            $details = $this->detailService->save(
                $this->detailMapper->fromArray(
                    [
                        'organisationUnitId' => $this->getActiveUserRootOu(),
                        'sku' => $sku,
                        $detail => $value,
                    ]
                )
            );
            $id = $details->getId();
        }

        return $id;
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
}
