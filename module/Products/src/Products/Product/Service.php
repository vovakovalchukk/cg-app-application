<?php
namespace Products\Product;

use CG\ETag\Exception\NotModified;
use CG\Product\Client\Service as ProductService;
use CG_UI\View\Table;
use CG\User\ActiveUserInterface;
use Zend\Di\Di;
use CG\User\Service as UserService;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\Account\Client\Service as AccountService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stock\Location\Service as StockLocationService;
use CG\Product\Filter as ProductFilter;
use CG\Listing\Collection as ListingCollection;

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

    protected $userService;
    protected $activeUserContainer;
    protected $di;
    protected $activeUserPreference;
    protected $userPreferenceService;
    protected $accountService;
    protected $organisationUnitService;
    protected $productService;
    protected $stockLocationService;

    public function __construct(
        UserService $userService,
        ActiveUserInterface $activeUserContainer,
        Di $di,
        UserPreferenceService $userPreferenceService,
        AccountService $accountService,
        OrganisationUnitService $organisationUnitService,
        ProductService $productService,
        StockLocationService $stockLocationService
    ) {
        $this->setProductService($productService)
            ->setUserService($userService)
            ->setActiveUserContainer($activeUserContainer)
            ->setDi($di)
            ->setUserPreferenceService($userPreferenceService)
            ->setAccountService($accountService)
            ->setOrganisationUnitService($organisationUnitService)
            ->setStockLocationService($stockLocationService);
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
            $stockEntity = $this->getStockLocationService()->fetch($stockLocationId);
            $stockEntity->setStoredEtag($eTag)
                ->setOnHand($totalQuantity);
            $this->getStockLocationService()->save($stockEntity);
        } catch (NotModified $e) {
            //No changes do nothing
        }
        return $stockEntity;
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
}
