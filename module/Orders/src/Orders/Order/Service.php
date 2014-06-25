<?php
namespace Orders\Order;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Table;
use CG_UI\View\Table\Column as TableColumn;
use CG_UI\View\Table\Rows as TableRows;
use CG\Order\Shared\StorageInterface;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter;
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

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const ORDER_TABLE_COL_PREF_KEY = 'order-columns';
    const ORDER_SIDEBAR_STATE_KEY = 'order-sidebar-state';
    const ORDER_FILTER_BAR_STATE_KEY = 'order-filter-bar-state';
    const ACCOUNTS_PAGE = 1;
    const ACCOUNTS_LIMIT = 'all';

    protected $orderClient;
    protected $tableService;
    protected $filterService;
    protected $userService;
    protected $activeUserContainer;
    protected $di;
    protected $activeUserPreference;
    protected $userPreferenceService;
    protected $accountService;
    protected $orderDispatcher;
    protected $orderCanceller;

    public function __construct(
        StorageInterface $orderClient,
        TableService $tableService,
        FilterService $filterService,
        UserService $userService,
        ActiveUserInterface $activeUserContainer,
        Di $di,
        UserPreferenceService $userPreferenceService,
        AccountService $accountService,
        OrderDispatcher $orderDispatcher,
        OrderCanceller $orderCanceller
    )
    {
        $this
            ->setOrderClient($orderClient)
            ->setTableService($tableService)
            ->setFilterService($filterService)
            ->setUserService($userService)
            ->setActiveUserContainer($activeUserContainer)
            ->setDi($di)
            ->setUserPreferenceService($userPreferenceService)
            ->configureOrderTable()
            ->setAccountService($accountService)
            ->setOrderDispatcher($orderDispatcher)
            ->setOrderCanceller($orderCanceller);
    }

    public function getOrdersArrayWithAccountDetails(OrderCollection $orderCollection, MvcEvent $event)
    {
        $accounts = $this->getAccountService()->fetchByOUAndStatus(
            $this->getActiveUser()->getOuList(),
            null,
            null,
            static::ACCOUNTS_LIMIT,
            static::ACCOUNTS_PAGE
        );

        $orders = [];
        foreach($orderCollection as $orderEntity) {
            $order = $orderEntity->toArray();

            $accountEntity = $accounts->getById($order['accountId']);
            if ($accountEntity) {
                $order['accountName'] = $accountEntity->getDisplayName();
            }

            $order['accountLink'] = $event->getRouter()->assemble(
                ['account' => $order['accountId']],
                ['name' => SettingsModule::ROUTE . '/' . ChannelController::ROUTE . '/' .ChannelController::ROUTE_CHANNELS.'/'. ChannelController::ACCOUNT_ROUTE]
            );

            $orders[] = $order;
        }


        $filterId = null;
        if ($orderCollection instanceof FilteredCollection) {
            $filterId = $orderCollection->getFilterId();
        }

        return [
            'orders' => $orders,
            'orderTotal' => (int) $orderCollection->getTotal(),
            'filterId' => $filterId,
        ];
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

    public function getOrdersTable()
    {
        return $this->getTableService()->getOrdersTable();
    }

    public function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    /**
     * @return FilterService
     */
    public function getFilterService()
    {
        return $this->filterService;
    }

    public function setOrderClient(StorageInterface $orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getOrderClient()
    {
        return $this->orderClient;
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
    public function getOrders(Filter $filter)
    {
        return $this->getOrderClient()->fetchCollectionByFilter($filter);
    }

    /**
     * @param array $orderIds
     * @throws NotFound
     * @return OrderCollection
     */
    public function getOrdersById(array $orderIds)
    {
        if (empty($orderIds)) {
            throw new NotFound();
        }

        $filter = $this->getDi()->newInstance(
            Filter::class,
            [
                'orderIds' => $orderIds,
                'organisationUnitId' => $this->getActiveUser()->getOuList(),
                'page' => 1,
                'limit' => 'all',
            ]
        );

        return $this->getOrders($filter);
    }

    /**
     * @return OrderCollection
     */
    public function getPreviewOrder()
    {
        $filter = $this->getDi()->newInstance(
            Filter::class,
            [
                'organisationUnitId' => $this->getActiveUser()->getOuList(),
                'page' => 1,
                'limit' => 1,
                'orderDirection' => 'ASC',
            ]
        );

        return $this->getOrders($filter);
    }

    public function getOrdersFromFilterId($filterId, $limit = 'all', $page = 1, $orderBy = null, $orderDirection = null)
    {
        return $this->getOrderClient()->fetchCollectionByFilterId(
            $filterId,
            $limit,
            $page,
            $orderBy,
            $orderDirection
        );
    }

    public function getOrder($orderId)
    {
        return $this->getOrderClient()->fetch($orderId);
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
        $visible = isset($preference[static::ORDER_SIDEBAR_STATE_KEY]) ? $preference[static::ORDER_SIDEBAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    public function isFilterBarVisible()
    {
        $preference = $this->getActiveUserPreference()->getPreference();
        $visible = isset($preference[static::ORDER_FILTER_BAR_STATE_KEY]) ? $preference[static::ORDER_FILTER_BAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    public function getOrderItemTable(Entity $order)
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

    public function getNamesFromOrderNotes(OrderNoteCollection $notes)
    {
        $itemNotes = array();
        foreach ($notes as $note) {
            $itemNote = $note->toArray();
            $itemNote["eTag"] = $note->getETag();
            $itemNotes[] = $itemNote;
        }
        $userIds = array();
        foreach ($itemNotes as $itemNote) {
            $userIds[] = $itemNote["userId"];
        }
        if (empty($userIds)) {
            return $itemNotes;
        }
        $userIds = array_unique($userIds);
        try {
            $users = $this->getUserService()->fetchCollection("all", null, null, null, $userIds);
            foreach ($itemNotes as &$note) {
                $user = $users->getById($note["userId"]);
                $note["author"] = $user->getFirstName() . " " . $user->getLastName();
            }
        } catch (NotFound $e) {
            //no users found for notes, don't return any authors
        }

        return $itemNotes;
    }

    public function saveOrder(Order $entity)
    {
        return $this->getOrderClient()->save($entity);
    }

    public function updateUserPrefOrderColumns(array $updatedColumns)
    {
        $storedColumns = $this->fetchUserPrefOrderColumns();
        foreach ($updatedColumns as $name => $on) {
            $storedColumns[$name] = $on;
        }
        $userPrefs = $this->getActiveUserPreference();
        $userPrefsPref = $userPrefs->getPreference();
        $columnPrefKey = static::ORDER_TABLE_COL_PREF_KEY;
        $userPrefsPref[$columnPrefKey] = $storedColumns;
        $userPrefs->setPreference($userPrefsPref);

        $this->getUserPreferenceService()->save($userPrefs);

        return $this;
    }

    protected function configureOrderTable()
    {
        $columns = $this->getOrdersTable()->getColumns();

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

        return $this;
    }

    protected function fetchUserPrefOrderColumns()
    {
        $columnPrefKey = static::ORDER_TABLE_COL_PREF_KEY;
        $userPrefsPref = $this->getActiveUserPreference()->getPreference();
        $storedColumns = (isset($userPrefsPref[$columnPrefKey]) ? $userPrefsPref[$columnPrefKey] : []);
        return $storedColumns;
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

    public function tagOrder($tag, Order $order)
    {
        $tags = array_fill_keys($order->getTags(), true);
        if (isset($tags[$tag])) {
            return;
        }

        $tags[$tag] = true;

        $this->saveOrder(
            $order->setTags(array_keys($tags))
        );
    }

    public function unTagOrders($tag, OrderCollection $orders)
    {
        $exception = new MultiException();

        foreach ($orders as $order) {
            try {
                $this->unTagOrder($tag, $order);
            } catch (Exception $orderException) {
                $exception->addOrderException($order->getId(), $orderException);
                $this->logException($orderException, 'error', __NAMESPACE__);
            }
        }

        if (count($exception) > 0) {
            throw $exception;
        }
    }

    public function unTagOrder($tag, Order $order)
    {
        $tags = array_fill_keys($order->getTags(), true);
        if (!isset($tags[$tag])) {
            return;
        }

        unset($tags[$tag]);

        $this->saveOrder(
            $order->setTags(array_keys($tags))
        );
    }

    public function dispatchOrders(OrderCollection $orders)
    {
        $exception = new MultiException();

        foreach ($orders as $order) {
            try {
                $this->dispatchOrder($order);
            } catch (Exception $orderException) {
                $exception->addOrderException($order->getId(), $orderException);
                $this->logException($orderException, 'error', __NAMESPACE__);
            }
        }

        if (count($exception) > 0) {
            throw $exception;
        }
    }

    public function dispatchOrder(Order $order)
    {
        $account = $this->getAccountService()->fetch($order->getAccountId());

        $this->saveOrder(
            $order->setStatus(OrderStatus::DISPATCHING)
        );

        $this->getOrderDispatcher()->generateJob($account, $order);
    }

    public function archiveOrders(OrderCollection $orders, $archive = true)
    {
        $exception = new MultiException();

        foreach ($orders as $order) {
            try {
                $this->archiveOrder($order, $archive);
            } catch (Exception $orderException) {
                $exception->addOrderException($order->getId(), $orderException);
                $this->logException($orderException, 'error', __NAMESPACE__);
            }
        }

        if (count($exception) > 0) {
            throw $exception;
        }
    }

    public function archiveOrder(Order $order, $archive = true)
    {
        return $this->getOrderClient()->archive(
            $order->setArchived($archive)
        );
    }

    public function cancelOrders(OrderCollection $orders, $type, $reason)
    {
        $exception = new MultiException();

        foreach ($orders as $order) {
            try {
                $this->cancelOrder($order, $type, $reason);
            } catch (Exception $orderException) {
                $exception->addOrderException($order->getId(), $orderException);
                $this->logException($orderException, 'error', __NAMESPACE__);
            }
        }

        if (count($exception) > 0) {
            throw $exception;
        }
    }

    public function cancelOrder(Order $order, $type, $reason)
    {
        $account = $this->getAccountService()->fetch($order->getAccountId());
        $status = OrderMapper::calculateOrderStatusFromCancelType($type);
        $cancel = $this->getCancelValue($order, $type, $reason);

        $this->saveOrder(
            $order->setStatus($status)
        );

        $this->getOrderCanceller()->generateJob($account, $order, $cancel);
    }

    /**
     * @param Order $order
     * @param string $type
     * @param string $reason
     * @return CancelValue
     */
    protected function getCancelValue(Order $order, $type, $reason)
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[] = [
                'orderItemId' => $item->getId(),
                'sku' => $item->getItemSku(),
                'quantity' => $item->getItemQuantity(),
                'amount' => $item->getIndividualItemPrice(),
                'unitPrice' => 0.00,
            ];
        }

        return $this->getDi()->newInstance(
            CancelValue::class,
            [
                'type' => $type,
                'timestamp' => date(DateTime::FORMAT),
                'reason' => $reason,
                'items' => $items,
                'shippingAmount' => $order->getShippingPrice(),
            ]
        );
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

    public function setOrderDispatcher(OrderDispatcher $orderDispatcher)
    {
        $this->orderDispatcher = $orderDispatcher;
        return $this;
    }

    /**
     * @return OrderDispatcher
     */
    public function getOrderDispatcher()
    {
        return $this->orderDispatcher;
    }

    public function setOrderCanceller(OrderCanceller $orderCanceller)
    {
        $this->orderCanceller = $orderCanceller;
        return $this;
    }

    /**
     * @return OrderCanceller
     */
    public function getOrderCanceller()
    {
        return $this->orderCanceller;
    }
}