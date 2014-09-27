<?php
namespace Products\Product;

use CG\ETag\Exception\NotModified;
use CG\Product\Client\Service as ProductService;
use CG_UI\View\Table;
use CG\User\ActiveUserInterface;
use Composer\DependencyResolver\Transaction;
use Zend\Di\Di;
use CG\User\Service as UserService;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\Account\Client\Service as AccountService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stock\Service as StockService;
use CG\Stock\Location\Service as StockLocationService;
use CG\Product\Filter as ProductFilter;
use CG\CGLib\Gearman\WorkerFunction\StockAdjustment as StockAdjustmentWorker;
use CG\CGLib\Gearman\Workload\StockAdjustment as StockAdjustmentWorkload;
use GearmanClient;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Source;
use SplObjectStorage;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const PRODUCT_TABLE_COL_PREF_KEY = 'product-columns';
    const PRODUCT_TABLE_COL_POS_PREF_KEY = 'product-column-positions';
    const PRODUCT_SIDEBAR_STATE_KEY = 'product-sidebar-state';
    const PRODUCT_FILTER_BAR_STATE_KEY = 'product-filter-bar-state';
    const ACCOUNTS_PAGE = 1;
    const ACCOUNTS_LIMIT = 'all';
    const LIMIT = 200;
    const PAGE = 1;

    const LOG_CODE = 'productsService';
    const LOG_GENERATING_JOBS = 'Generating gearman jobs for stock Id %s';
    const LOG_FINISHED_GENERATING_JOBS = 'Finished generating gearman jobs for stock Id %s, now saving to our database';

    protected $userService;
    protected $activeUserContainer;
    protected $di;
    protected $activeUserPreference;
    protected $userPreferenceService;
    protected $accountService;
    protected $organisationUnitService;
    protected $productService;
    protected $stockLocationService;
    protected $stockService;
    protected $stockAdjustmentWorker;
    protected $gearmanClient;

    public function __construct(
        UserService $userService,
        ActiveUserInterface $activeUserContainer,
        Di $di,
        UserPreferenceService $userPreferenceService,
        AccountService $accountService,
        OrganisationUnitService $organisationUnitService,
        ProductService $productService,
        StockLocationService $stockLocationService,
        StockService $stockService,
        StockAdjustmentWorker $stockAdjustmentWorker,
        GearmanClient $gearmanClient
    ) {
        $this->setProductService($productService)
            ->setUserService($userService)
            ->setActiveUserContainer($activeUserContainer)
            ->setDi($di)
            ->setUserPreferenceService($userPreferenceService)
            ->setAccountService($accountService)
            ->setOrganisationUnitService($organisationUnitService)
            ->setStockLocationService($stockLocationService)
            ->setStockService($stockService)
            ->setStockAdjustmentWorker($stockAdjustmentWorker)
            ->setGearmanClient($gearmanClient);
    }

    public function fetchProducts(ProductFilter $productFilter)
    {
        $parentProductIds = [0];
        $productFilter->setLimit(static::LIMIT)
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
            $currentQuantity = $stockLocationEntity->getOnHand();
            $stockLocationEntity->setStoredEtag($eTag)
                ->setOnHand($totalQuantity);
            $adjustQuantity = $totalQuantity - $currentQuantity;
            $sign = $adjustQuantity < 0 ? StockAdjustment::OPERATOR_DEC : StockAdjustment::OPERATOR_INC;
            $this->getStockLocationService()->save($stockLocationEntity);

            $stockEntity = $this->getStockService()->fetch($stockLocationEntity->getStockId());
            $this->logDebug(static::LOG_GENERATING_JOBS, [$stockEntity->getId()], static::LOG_CODE);

            $stockAdjustment = new StockAdjustment(StockAdjustment::TYPE_ONHAND, $adjustQuantity, $sign);

            $activeUser = $this->getActiveUserContainer()->getActiveUser();

            $userId = $activeUser->getId();

            $stockSource = new Source('user', $userId);

            $stockAdjustmentSplObj = new SplObjectStorage();
            $stockAdjustmentSplObj->attach($stockAdjustment);

            $workload = new StockAdjustmentWorkload(
                $stockEntity,
                $stockSource,
                $stockAdjustmentSplObj,
                $stockLocationEntity->getLocationId()
            );

            $this->getGearmanClient()->doBackground('stockAdjustment', serialize($workload));

            $this->logDebug(static::LOG_GENERATING_JOBS, [$stockEntity->getId()], static::LOG_CODE);
        } catch (NotModified $e) {
            //No changes do nothing
        }
        return $stockLocationEntity;
    }

    public function deleteProductsById(array $productIds)
    {
        $filter = new ProductFilter(static::ACCOUNTS_LIMIT, static::PAGE, [], null, [], $productIds);
        $products = $this->getProductService()->fetchCollectionByFilter($filter);
        foreach ($products as $product) {
            $this->getProductService()->remove($product);
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

    protected function setStockLocationService($stockLocationService)
    {
        $this->stockLocationService = $stockLocationService;
        return $this;
    }

    protected function getStockLocationService()
    {
        return $this->stockLocationService;
    }

    protected function getStockService()
    {
        return $this->stockService;
    }

    public function setStockService(StockService $stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }

    protected function getStockAdjustmentWorker()
    {
        return $this->stockAdjustmentWorker;
    }

    public function setStockAdjustmentWorker(StockAdjustmentWorker $stockAdjustmentWorker)
    {
        $this->stockAdjustmentWorker = $stockAdjustmentWorker;
        return $this;
    }

    protected function getGearmanClient()
    {
        return $this->gearmanClient;
    }

    public function setGearmanClient(GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
        return $this;
    }
}
