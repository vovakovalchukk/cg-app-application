<?php
namespace Products\Product;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Table;
use CG_UI\View\Table\Column as TableColumn;
use CG_UI\View\Table\Rows as TableRows;
use CG\Order\Shared\StorageInterface;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\Order\Shared\Entity;
use CG\Order\Shared\Item\Entity as ItemEntity;
use Zend\Di\Di;
use Zend\I18n\View\Helper\CurrencyFormat;
use CG\User\Service as UserService;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Note\Collection as OrderNoteCollection;
use CG\UserPreference\Client\Service as UserPreferenceService;
use Settings\Module as SettingsModule;
use Settings\Controller\ChannelController;
use CG\Account\Client\Service as AccountService;
use Zend\Mvc\MvcEvent;
use CG\Stdlib\DateTime;
use CG\Order\Client\Collection as FilteredCollection;
use CG\Stdlib\PageLimit;
use CG\Stdlib\OrderBy;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Channel\Gearman\Generator\Order\Dispatch as OrderDispatcher;
use CG\Channel\Gearman\Generator\Order\Cancel as OrderCanceller;
use Orders\Order\Exception\MultiException;
use Exception;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Order\Shared\Status as OrderStatus;
use CG\OrganisationUnit\Service as OrganisationUnitService;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const PRODUCT_TABLE_COL_PREF_KEY = 'product-columns';
    const PRODUCT_TABLE_COL_POS_PREF_KEY = 'product-column-positions';
    const PRODUCT_SIDEBAR_STATE_KEY = 'product-sidebar-state';
    const PRODUCT_FILTER_BAR_STATE_KEY = 'product-filter-bar-state';
    const ACCOUNTS_PAGE = 1;
    const ACCOUNTS_LIMIT = 'all';

    protected $productClient;
    protected $tableService;
    protected $userService;
    protected $activeUserContainer;
    protected $di;
    protected $activeUserPreference;
    protected $userPreferenceService;
    protected $accountService;
    protected $organisationUnitService;

    public function __construct(
        StorageInterface $productClient,
        TableService $tableService,
        UserService $userService,
        ActiveUserInterface $activeUserContainer,
        Di $di,
        UserPreferenceService $userPreferenceService,
        AccountService $accountService,
        OrganisationUnitService $organisationUnitService
    )
    {
        $this
            ->setProductClient($productClient)
            ->setTableService($tableService)
            ->setUserService($userService)
            ->setActiveUserContainer($activeUserContainer)
            ->setDi($di)
            ->setUserPreferenceService($userPreferenceService)
            ->configureOrderTable()
            ->setAccountService($accountService)
            ->setOrganisationUnitService($organisationUnitService);
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }

    public function setTableService(TableService $tableService)
    {
        $this->tableService = $tableService;
        return $this;
    }

    /**
     * @return TableService
     */
    public function getTableService()
    {
        return $this->tableService;
    }

    public function getProductsTable()
    {
        return $this->getTableService()->getProductsTable();
    }

    public function setProductClient(StorageInterface $productClient)
    {
        $this->productClient = $productClient;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getProductClient()
    {
        return $this->productClient;
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    /**
     * UserService
     */
    public function getUserService()
    {
        return $this->userService;
    }

    /**
     * @return ActiveUserInterface
     */
    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    /**
     * @param Filter $filter
     * @return OrderCollection
     */
    public function getProducts(Filter $filter)
    {
        return $this->getProductClient()->fetchCollectionByFilter($filter);
    }


    public function getProduct($orderId)
    {
        return $this->getProductClient()->fetch($orderId);
    }

    public function setUserPreferenceService(UserPreferenceService $userPreferenceService)
    {
        $this->userPreferenceService = $userPreferenceService;
        return $this;
    }

    /**
     * @return UserPreferenceService
     */
    public function getUserPreferenceService()
    {
        return $this->userPreferenceService;
    }

    public function getActiveUserPreference()
    {
        if (!isset($this->activeUserPreference)) {
            $activeUserId = $this->getActiveUser()->getId();
            $this->activeUserPreference = $this->getUserPreferenceService()->fetch($activeUserId);
        }

        return $this->activeUserPreference;
    }

    public function isSidebarVisible()
    {
        $preference = $this->getActiveUserPreference()->getPreference();
        $visible = isset($preference[static::PRODUCT_SIDEBAR_STATE_KEY]) ? $preference[static::PRODUCT_SIDEBAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    public function getProductItemTable(Entity $order)
    {
        $getDiscountTotal = function (ItemEntity $entity) {
            return $entity->getIndividualItemDiscountPrice() * $entity->getItemQuantity();
        };

        $getTaxTotal = function (ItemEntity $entity) {
            return $entity->getItemTaxPercentage() * $entity->getItemQuantity();
        };

        $getLineTotal = function (ItemEntity $entity) {
            return $entity->getIndividualItemPrice() * $entity->getItemQuantity();
        };

        $numberFormat = $this->getDi()->get(CurrencyFormat::class);
        $currencyCode = $order->getCurrencyCode();
        $currencyFormatter = function (ItemEntity $entity, $value) use ($numberFormat, $currencyCode) {
            /** @var $numberFormat CurrencyFormat */
            return $numberFormat($value, $currencyCode);
        };

        $columns = [
            ['name' => 'SKU', 'class' => '', 'getter' => 'getItemSku', 'callback' => null],
            ['name' => 'Product Name', 'class' => '', 'getter' => 'getItemName', 'callback' => null],
            ['name' => 'Quantity', 'class' => 'right', 'getter' => 'getItemQuantity', 'callback' => null],
            ['name' => 'Individual Price', 'class' => 'right', 'getter' => 'getIndividualItemPrice', 'callback' => $currencyFormatter],
            ['name' => 'Individual Discount', 'class' => 'right', 'getter' => 'getIndividualItemDiscountPrice', 'callback' => $currencyFormatter],
            ['name' => 'Tax', 'class' => 'right', 'getter' => 'getItemTaxPercentage', 'callback' => null],
            ['name' => 'Discount Total', 'class' => 'right', 'getter' => $getDiscountTotal, 'callback' => $currencyFormatter],
            ['name' => 'Tax Total', 'class' => 'right', 'getter' => $getTaxTotal, 'callback' => $currencyFormatter],
            ['name' => 'Line Total', 'class' => 'right', 'getter' => $getLineTotal, 'callback' => $currencyFormatter],
        ];

        $table = $this->getDi()->newInstance(Table::class);
        $mapping = [];
        foreach ($columns as $column) {
            $table->addColumn($this->getDi()->newInstance(TableColumn::class, ["name" => $column["name"], "class" => $column["class"]]));
            $mapping[$column["name"]] = ["getter" => $column["getter"], "callback" => $column["callback"]];
        }
        $rows = $this->getDi()->newInstance(TableRows::class, ["data" => $order->getItems(), "mapping" => $mapping]);
        $table->setRows($rows);
        $table->setTemplate('table/standard');
        return $table;
    }

    public function updateUserPrefOrderColumns(array $updatedColumns)
    {
        $storedColumns = $this->fetchUserPrefOrderColumns();
        foreach ($updatedColumns as $name => $on) {
            $storedColumns[$name] = $on;
        }

        $columnPrefKey = static::PRODUCT_TABLE_COL_PREF_KEY;
        $this->saveUserPrefItem($columnPrefKey, $storedColumns);

        return $this;
    }

    public function updateUserPrefOrderColumnPositions(array $columnPositions)
    {
        $columnPrefKey = static::PRODUCT_TABLE_COL_POS_PREF_KEY;
        $this->saveUserPrefItem($columnPrefKey, $columnPositions);

        return $this;
    }

    protected function configureOrderTable()
    {
        $columns = $this->getProductsTable()->getColumns();

        $associativeColumns = [];
        foreach ($columns as $column) {
            $associativeColumns[$column->getColumn()] = $column;
        }

        $columnPrefs = $this->fetchUserPrefOrderColumns();
        foreach ($columnPrefs as $name => $on) {
            if (!isset($associativeColumns[$name])) {
                continue;
            }
            $associativeColumns[$name]->setVisible(
                filter_var($on, FILTER_VALIDATE_BOOLEAN)
            );
        }

        $columnPosPrefs = $this->fetchUserPrefOrderColumnPositions();
        foreach ($columnPosPrefs as $name => $pos) {
            if (!isset($associativeColumns[$name])) {
                continue;
            }
            $associativeColumns[$name]->setOrder($pos);
        }
        $this->getProductsTable()->reorderColumns();

        return $this;
    }

    protected function fetchUserPrefOrderColumns()
    {
        $columnPrefKey = static::PRODUCT_TABLE_COL_PREF_KEY;
        return $this->fetchUserPrefItem($columnPrefKey);
    }

    protected function fetchUserPrefOrderColumnPositions()
    {
        $columnPrefKey = static::PRODUCT_TABLE_COL_POS_PREF_KEY;
        return $this->fetchUserPrefItem($columnPrefKey);
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

    public function tagOrders($tag, OrderCollection $orders)
    {
        $exception = new MultiException();

        foreach ($orders as $order) {
            try {
                $this->tagOrder($tag, $order);
            } catch (Exception $orderException) {
                $exception->addOrderException($order->getId(), $orderException);
                $this->logException($orderException, 'error', __NAMESPACE__);
            }
        }

        if (count($exception) > 0) {
            throw $exception;
        }
    }

    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @return AccountService
     */
    public function getAccountService()
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

    public function isFilterBarVisible()
    {
        $preference = $this->getActiveUserPreference()->getPreference();
        $visible = isset($preference[static::PRODUCT_FILTER_BAR_STATE_KEY]) ? $preference[static::PRODUCT_FILTER_BAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }
}