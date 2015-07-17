<?php
namespace Products\Product;

use CG\Http\Exception\Exception3xx\NotModified as HttpNotModified;
use CG\ETag\Exception\NotModified;
use CG\Product\Client\Service as ProductService;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG_UI\View\Table;
use CG\User\ActiveUserInterface;
use Zend\Di\Di;
use CG\User\Service as UserService;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\Account\Client\Service as AccountService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Location\Service as StockLocationService;
use CG\Stock\Service as StockService;
use CG\Product\Filter as ProductFilter;
use CG\Product\Filter\Mapper as ProductFilterMapper;
use CG\Product\Entity as Product;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Auditor as StockAuditor;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;

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
    protected $activeUserContainer;
    protected $di;
    protected $activeUserPreference;
    protected $userPreferenceService;
    protected $accountService;
    protected $organisationUnitService;
    protected $productService;
    protected $productFilterMapper;
    protected $stockService;
    protected $stockLocationService;
    protected $stockAuditor;
    protected $intercomEventService;

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
        IntercomEventService $intercomEventService
    ) {
        $this->setProductService($productService)
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
            ->setIntercomEventService($intercomEventService);
    }

    public function fetchProducts(ProductFilter $productFilter, $parentProductIds = [0], $limit = self::LIMIT)
    {
        $productFilter
            ->setLimit($limit)
            ->setPage(static::PAGE)
            ->setOrganisationUnitId($this->getActiveUserContainer()->getActiveUser()->getOuList())
            ->setParentProductId($parentProductIds);
        $products = $this->getProductService()->fetchCollectionByFilter($productFilter);
        return $products;
    }

    public function updateStock($stockLocationId, $eTag, $totalQuantity)
    {
        try {
            $stockLocationEntity = $this->getStockLocationService()->fetch($stockLocationId);
            $this->auditStockUpdate($stockLocationEntity, $totalQuantity);
            $stockLocationEntity->setStoredEtag($eTag)
                ->setOnHand($totalQuantity);
            $this->getStockLocationService()->save($stockLocationEntity);
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
            if($this->isLastOfStock($product)) {
                try {
                    $ouList = $this->getActiveUserContainer()->getActiveUser()->getOuList();
                    $stock = $this->getStockService()->fetchCollectionByPaginationAndFilters(
                        null,
                        null,
                        [],
                        $ouList,
                        [$product->getSku()],
                        []
                    );
                    foreach($stock as $entity) {
                        $this->getStockService()->remove($entity);
                    }
                } catch (NotFound $e) {
                    // No stock to remove, no problem
                    // If we were expecting there to be stock then log it
                    if ($product->getParentProductId() > 0 || count($product->getVariations()) == 0) {
                        $this->logNotice(static::LOG_NO_STOCK_TO_DELETE, [$product->getId()], static::LOG_CODE);
                    }
                }
            }
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

    protected function isLastOfStock(Product $product)
    {
        $filter = $this->getProductFilterMapper()->fromArray([
            'limit' => 2,
            'page' => 1,
            'organisationUnitId' => $this->getActiveUserContainer()->getActiveUser()->getOuList(),
            'searchTerm' => null,
            'parentProductId' => [],
            'id' => [],
            'deleted' => null,
            'sku' => [$product->getSku()]
        ]);
        $products = $this->getProductService()->fetchCollectionByFilter($filter);

        if(count($products) == 1) {
            return true;
        }

        return false;
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

    protected function auditStockUpdate($stockLocationEntity, $newTotal)
    {
        $oldTotal = $stockLocationEntity->getOnHand();
        if ($newTotal > $oldTotal) {
            $diff = $newTotal - $oldTotal;
            $operator = StockAdjustment::OPERATOR_INC;
        } else {
            $diff = $oldTotal - $newTotal;
            $operator = StockAdjustment::OPERATOR_DEC;
        }
        $adjustment = new StockAdjustment(StockAdjustment::TYPE_ONHAND, $diff, $operator);
        $stock = $this->stockService->fetch($stockLocationEntity->getStockId());
        $this->getStockAuditor()->userAdjustment(
            $adjustment,
            $stock->getSku(),
            $stock->getOrganisationUnitId()
        );
    }

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

    protected function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

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

    protected function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    protected function setStockLocationService(StockLocationService $stockLocationService)
    {
        $this->stockLocationService = $stockLocationService;
        return $this;
    }

    protected function getStockLocationService()
    {
        return $this->stockLocationService;
    }

    protected function setStockService(StockService $stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }

    protected function getStockService()
    {
        return $this->stockService;
    }

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

    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }
}
