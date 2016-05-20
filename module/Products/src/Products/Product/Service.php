<?php
namespace Products\Product;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Channel\Type as ChannelType;
use CG\ETag\Exception\NotModified;
use CG\Http\Exception\Exception3xx\NotModified as HttpNotModified;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Client\Service as ProductService;
use CG\Product\Detail\Client\Service as DetailService;
use CG\Product\Detail\Entity as Details;
use CG\Product\Detail\Mapper as DetailMapper;
use CG\Product\Filter as ProductFilter;
use CG\Product\Filter\Mapper as ProductFilterMapper;
use CG\Product\StockMode;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Adjustment\Service as StockAdjustmentService;
use CG\Stock\Auditor as StockAuditor;
use CG\Stock\Location\Service as StockLocationService;
use CG\Stock\Service as StockService;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\User\Service as UserService;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG_UI\View\Table;
use Zend\Di\Di;

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
    const LOG_CODE = 'ProductProductService';
    const LOG_NO_STOCK_TO_DELETE = 'No stock found to remove for Product %s when deleting it';
    const STAT_STOCK_UPDATE_MANUAL = 'stock.update.manual.%d.%d';
    const EVENT_MANUAL_STOCK_CHANGE = 'Manual Stock Change';
    const LOG_PRODUCT_NOT_FOUND = 'Tried saving product %s with taxRateId %s but the product could not be found';

    protected $userService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    protected $di;
    protected $activeUserPreference;
    protected $userPreferenceService;
    /** @var AccountService */
    protected $accountService;
    protected $organisationUnitService;
    protected $productService;
    protected $productFilterMapper;
    protected $stockService;
    protected $stockLocationService;
    protected $stockAuditor;
    protected $intercomEventService;
    /** @var StockAdjustmentService */
    protected $stockAdjustmentService;
    /** @var DetailService $detailService */
    protected $detailService;
    /** @var DetailMapper $detailMapper */
    protected $detailMapper;

    public function __construct(
        UserService $userService,
        ActiveUserInterface $activeUserContainer,
        Di $di,
        UserPreferenceService $userPreferenceService,
        AccountService $accountService,
        OrganisationUnitService $organisationUnitService,
        ProductFilterMapper $productFilterMapper,
        ProductService $productService,
        StockLocationService $stockLocationService,
        StockService $stockService,
        StockAuditor $stockAuditor,
        IntercomEventService $intercomEventService,
        StockAdjustmentService $stockAdjustmentService,
        DetailService $detailService,
        DetailMapper $detailMapper
    ) {
        $this
            ->setProductService($productService)
            ->setUserService($userService)
            ->setProductFilterMapper($productFilterMapper)
            ->setActiveUserContainer($activeUserContainer)
            ->setDi($di)
            ->setUserPreferenceService($userPreferenceService)
            ->setAccountService($accountService)
            ->setOrganisationUnitService($organisationUnitService)
            ->setStockService($stockService)
            ->setStockLocationService($stockLocationService)
            ->setStockAuditor($stockAuditor)
            ->setIntercomEventService($intercomEventService)
            ->setStockAdjustmentService($stockAdjustmentService)
            ->setDetailService($detailService)
            ->setDetailMapper($detailMapper);
    }

    public function fetchProducts(ProductFilter $productFilter, $limit = self::LIMIT, $page = self::PAGE)
    {
        $productFilter
            ->setLimit($limit)
            ->setPage($page)
            ->setOrganisationUnitId($this->getActiveUserContainer()->getActiveUser()->getOuList());
        $products = $this->getProductService()->fetchCollectionByFilter($productFilter);
        return $products;
    }

    public function updateStock($stockLocationId, $eTag, $totalQuantity)
    {
        try {
            $stockLocationEntity = $this->getStockLocationService()->fetch($stockLocationId);

            $adjustment = $this->createAndAuditStockAdjustment($stockLocationEntity, $totalQuantity);
            $stockLocationEntity->setStoredEtag($eTag);
            $this->stockAdjustmentService->applyAdjustmentAndSave($adjustment, $stockLocationEntity);
            $this->statsIncrement(
                static::STAT_STOCK_UPDATE_MANUAL, [
                    $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId(),
                    $this->getActiveUserContainer()->getActiveUser()->getId()
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
        $this->getIntercomEventService()->save($event);
    }

    public function deleteProductsById(array $productIds)
    {
        $filter = new ProductFilter(static::ACCOUNTS_LIMIT, static::PAGE, [], null, [], $productIds);
        $products = $this->getProductService()->fetchCollectionByFilter($filter);
        foreach ($products as $product) {
            $this->getProductService()->hardRemove($product);
        }
    }

    public function saveProductTaxRateId($productId, $taxRateId)
    {
        try {
            $product = $this->getProductService()->fetch($productId);
            $this->getProductService()->save($product->setTaxRateId($taxRateId));
        } catch (NotFound $e) {
            $this->logWarning(static::LOG_PRODUCT_NOT_FOUND, [$productId, $taxRateId], static::LOG_CODE);
        }  catch (HttpNotModified $e) {
            // Do nothing
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

        $this->getUserPreferenceService()->save($userPrefs);
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
        $stock = $this->stockService->fetch($stockLocationEntity->getStockId());
        $this->getStockAuditor()->userAdjustment(
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

    /**
     * @return self
     */
    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->productService;
    }

    /**
     * @return self
     */
    protected function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di
     */
    protected function getDi()
    {
        return $this->di;
    }

    /**
     * @return self
     */
    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function getActiveUserPreference()
    {
        if (!isset($this->activeUserPreference)) {
            $activeUserId = $this->getActiveUser()->getId();
            $this->activeUserPreference = $this->getUserPreferenceService()->fetch($activeUserId);
        }

        return $this->activeUserPreference;
    }

    /**
     * @return self
     */
    protected function setUserService(UserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    /**
     * UserService
     */
    protected function getUserService()
    {
        return $this->userService;
    }

    /**
     * @return ActiveUserInterface
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @return User
     */
    protected function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    protected function getActiveUserRootOu()
    {
        return $this->getOrganisationUnitService()->getRootOuIdFromOuId(
            $this->getActiveUser()->getOrganisationUnitId()
        );
    }

    /**
     * @return self
     */
    protected function setUserPreferenceService(UserPreferenceService $userPreferenceService)
    {
        $this->userPreferenceService = $userPreferenceService;
        return $this;
    }

    /**
     * @return UserPreferenceService
     */
    protected function getUserPreferenceService()
    {
        return $this->userPreferenceService;
    }

    /**
     * @return self
     */
    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @return AccountService
     */
    protected function getAccountService()
    {
        return $this->accountService;
    }

    /**
     * @return OrganisationUnitService
     */
    protected function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    /**
     * @return self
     */
    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setStockLocationService(StockLocationService $stockLocationService)
    {
        $this->stockLocationService = $stockLocationService;
        return $this;
    }

    protected function getStockLocationService()
    {
        return $this->stockLocationService;
    }

    /**
     * @return self
     */
    protected function setStockService(StockService $stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }

    protected function getStockService()
    {
        return $this->stockService;
    }

    /**
     * @return self
     */
    protected function setProductFilterMapper(ProductFilterMapper $productFilterMapper)
    {
        $this->productFilterMapper = $productFilterMapper;
        return $this;
    }

    protected function getProductFilterMapper()
    {
        return $this->productFilterMapper;
    }

    /**
     * @return self
     */
    public function setStockAuditor(StockAuditor $stockAuditor)
    {
        $this->stockAuditor = $stockAuditor;
        return $this;
    }

    /**
     * @return StockAuditor
     */
    protected function getStockAuditor()
    {
        return $this->stockAuditor;
    }

    protected function getIntercomEventService()
    {
        return $this->intercomEventService;
    }

    /**
     * @return self
     */
    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setStockAdjustmentService(StockAdjustmentService $stockAdjustmentService)
    {
        $this->stockAdjustmentService = $stockAdjustmentService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setDetailService(DetailService $detailService)
    {
        $this->detailService = $detailService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setDetailMapper(DetailMapper $detailMapper)
    {
        $this->detailMapper = $detailMapper;
        return $this;
    }
}
