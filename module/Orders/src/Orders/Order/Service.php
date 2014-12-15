<?php
namespace Orders\Order;

use CG\Account\Client\Service as AccountService;
use CG\Channel\Action\Order\Service as ActionService;
use CG\Channel\Action\Order\MapInterface as ActionMapInterface;
use CG\Channel\Carrier;
use CG\Channel\Gearman\Generator\Order\Cancel as OrderCanceller;
use CG\Channel\Gearman\Generator\Order\Dispatch as OrderDispatcher;
use CG\Channel\Type;
use CG\Http\Exception\Exception3xx\NotModified as NotModifiedException;
use CG\Order\Client\Collection as FilteredCollection;
use CG\Order\Service\Filter;
use CG\Order\Service\Filter\StorageInterface as FilterClient;
use CG\Order\Shared\Cancel\Item as CancelItem;
use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Shared\Item\Entity as ItemEntity;
use CG\Order\Shared\Item\StorageInterface as OrderItemClient;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Shared\Note\Collection as OrderNoteCollection;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\Order\Shared\Status as OrderStatus;
use CG\Order\Shared\StorageInterface;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\User\Service as UserService;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG_UI\View\Filters\Service as FilterService;
use CG_UI\View\Table;
use CG_UI\View\Table\Column as TableColumn;
use CG_UI\View\Table\Rows as TableRows;
use Exception;
use Orders\Order\Exception\MultiException;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Di\Di;
use Zend\I18n\View\Helper\CurrencyFormat;
use Zend\Mvc\MvcEvent;
use CG\Order\Shared\Cancel\Value as Cancel;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    const ORDER_TABLE_COL_PREF_KEY = 'order-columns';
    const ORDER_TABLE_COL_POS_PREF_KEY = 'order-column-positions';
    const ORDER_SIDEBAR_STATE_KEY = 'order-sidebar-state';
    const ORDER_FILTER_BAR_STATE_KEY = 'order-filter-bar-state';
    const ACCOUNTS_PAGE = 1;
    const ACCOUNTS_LIMIT = 'all';
    const LOG_CODE = 'OrderModuleService';
    const LOG_UNDISPATCHABLE = 'Order %s has been flagged for dispatch but it is not in a dispatchable status (%s)';
    const LOG_DISPATCHING = 'Dispatching Order %s';
    const STAT_ORDER_DISPATCHED = 'order.dispatched.%s';
    const STAT_ORDER_CANCELLED = 'order.cancelled.%s';
    const STAT_ORDER_REFUNDED = 'order.refunded.%s';
    const STAT_ORDER_ARCHIVED = 'order.archived.%s';
    const STAT_ORDER_TAG_CREATED = 'order.tag.created.%s';

    protected $orderClient;
    protected $orderItemClient;
    protected $filterClient;
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
    protected $shippingConversionService;
    protected $carriers;
    protected $organisationUnitService;
    protected $actionService;

    public function __construct(
        StorageInterface $orderClient,
        OrderItemClient $orderItemClient,
        FilterClient $filterClient,
        TableService $tableService,
        FilterService $filterService,
        UserService $userService,
        ActiveUserInterface $activeUserContainer,
        Di $di,
        UserPreferenceService $userPreferenceService,
        AccountService $accountService,
        OrderDispatcher $orderDispatcher,
        OrderCanceller $orderCanceller,
        ShippingConversionService $shippingConversionService,
        Carrier $carriers,
        OrganisationUnitService $organisationUnitService,
        ActionService $actionService
    )
    {
        $this
            ->setOrderClient($orderClient)
            ->setOrderItemClient($orderItemClient)
            ->setFilterClient($filterClient)
            ->setTableService($tableService)
            ->setFilterService($filterService)
            ->setUserService($userService)
            ->setActiveUserContainer($activeUserContainer)
            ->setDi($di)
            ->setUserPreferenceService($userPreferenceService)
            ->configureOrderTable()
            ->setAccountService($accountService)
            ->setOrderDispatcher($orderDispatcher)
            ->setOrderCanceller($orderCanceller)
            ->setShippingConversionService($shippingConversionService)
            ->setCarriers($carriers)
            ->setOrganisationUnitService($organisationUnitService)
            ->setActionService($actionService);
    }

    public function alterOrderTable(OrderCollection $orderCollection, MvcEvent $event)
    {
        $orders = $orderCollection->toArray();

        try {
            $orders = $this->getOrdersArrayWithShippingAliases($orders);
        } catch (NotFound $e) {
            // do nothing
        }
        try {
            $orders = $this->getOrdersArrayWithAccountDetails($orders, $event);
        } catch (NotFound $e) {
            // do nothing
        }
        $orders = $this->getOrdersArrayWithSanitisedStatus($orders);
        
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

    public function getOrdersArrayWithShippingAliases(array $orders)
    {
        $organisationUnit = $this->getOrganisationUnitService()
                                 ->fetch($this->getActiveUserContainer()
                                              ->getActiveUserRootOrganisationUnitId()
            );

        foreach($orders as $index => $order) {
            $shippingAlias = $this->getShippingConversionService()
                                  ->fromMethodToAlias($order['shippingMethod'],
                                                      $organisationUnit
                );
            $orders[$index]['shippingMethod'] = $shippingAlias ? $shippingAlias->getName() : $orders[$index]['shippingMethod'];
        }
        return $orders;
    }

    public function getOrdersArrayWithAccountDetails(array $orders, MvcEvent $event)
    {
        $accounts = $this->getAccountService()->fetchByOUAndStatus(
            $this->getActiveUser()->getOuList(),
            null,
            null,
            static::ACCOUNTS_LIMIT,
            static::ACCOUNTS_PAGE,
            Type::SALES
        );

        foreach($orders as $index => $order) {
            $accountEntity = $accounts->getById($order['accountId']);
            if ($accountEntity) {
                $order['accountName'] = $accountEntity->getDisplayName();
            }

            $order['accountLink'] = $event->getRouter()->assemble(
                ['account' => $order['accountId'], 'type' => Type::SALES],
                ['name' => SettingsModule::ROUTE . '/' . ChannelController::ROUTE . '/' .ChannelController::ROUTE_CHANNELS.'/'. ChannelController::ROUTE_ACCOUNT]
            );

            $orders[$index] = $order;
        }
        return $orders;
    }

    protected function getOrdersArrayWithSanitisedStatus(array $orders)
    {
        foreach ($orders as $index => $order) {
            $orders[$index]['status'] = str_replace(['_', '-'], ' ', $orders[$index]['status']);
            $orders[$index]['statusClass'] = str_replace(' ', '-', $orders[$index]['status']);
        }
        return $orders;
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
    public function getOrdersById(array $orderIds, $limit = 'all', $page = 1, $orderBy = null, $orderDirection = null)
    {
        if (empty($orderIds)) {
            throw new NotFound();
        }

        $filter = $this->getDi()->newInstance(
            Filter::class,
            [
                'orderIds' => $orderIds,
                'organisationUnitId' => $this->getActiveUser()->getOuList(),
                'page' => $page,
                'limit' => $limit,
                'orderBy' => $orderBy,
                'orderDirection' => $orderDirection
            ]
        );

        $filter = $this->getFilterClient()->save($filter);
        return $this->getOrdersFromFilterId($filter->getId(), $limit, $page, $orderBy, $orderDirection);
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

    public function getOrderItemTable(OrderEntity $order)
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

        $linkFormatter = function (ItemEntity $entity, $value) {
            if(empty($entity->getUrl())) {
                return $value;
            }
            return '<a href="' . $entity->getUrl() . '" target="_blank">' . $value . '</a>';
        };

        $columns = [
            ['name' => 'SKU', 'class' => '', 'getter' => 'getItemSku', 'callback' => null],
            ['name' => 'Product Name', 'class' => '', 'getter' => 'getItemName', 'callback' => $linkFormatter],
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

    public function saveOrder(OrderEntity $entity)
    {
        return $this->getOrderClient()->save($entity);
    }

    public function updateUserPrefOrderColumns(array $updatedColumns)
    {
        $storedColumns = $this->fetchUserPrefOrderColumns();
        foreach ($updatedColumns as $name => $on) {
            $storedColumns[$name] = $on;
        }

        $columnPrefKey = static::ORDER_TABLE_COL_PREF_KEY;
        $this->saveUserPrefItem($columnPrefKey, $storedColumns);

        return $this;
    }

    public function updateUserPrefOrderColumnPositions(array $columnPositions)
    {
        $columnPrefKey = static::ORDER_TABLE_COL_POS_PREF_KEY;
        $this->saveUserPrefItem($columnPrefKey, $columnPositions);

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

        $columnPosPrefs = $this->fetchUserPrefOrderColumnPositions();
        foreach ($columnPosPrefs as $name => $pos) {
            if (!isset($associativeColumns[$name])) {
                continue;
            }
            $associativeColumns[$name]->setOrder($pos);
        }
        $this->getOrdersTable()->reorderColumns();

        return $this;
    }

    protected function fetchUserPrefOrderColumns()
    {
        $columnPrefKey = static::ORDER_TABLE_COL_PREF_KEY;
        return $this->fetchUserPrefItem($columnPrefKey);
    }

    protected function fetchUserPrefOrderColumnPositions()
    {
        $columnPrefKey = static::ORDER_TABLE_COL_POS_PREF_KEY;
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

    public function tagOrder($tag, OrderEntity $order)
    {
        $tags = array_fill_keys($order->getTags(), true);
        if (isset($tags[$tag])) {
            return;
        }

        $tags[$tag] = true;

        $this->saveOrder(
            $order->setTags(array_keys($tags))
        );
        $this->getStatsClient()->stat(
            sprintf(static::STAT_ORDER_TAG_CREATED, $order->getChannel()),
            $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId()
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

    public function unTagOrder($tag, OrderEntity $order)
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

    public function dispatchOrder(OrderEntity $order)
    {
        $actions = $this->getActionService()->getAvailableActionsForOrder($order);
        if (!array_key_exists(ActionMapInterface::DISPATCH, array_flip($actions))) {
            $this->logWarning(static::LOG_UNDISPATCHABLE, [$order->getId(), $order->getStatus()], static::LOG_CODE);
            return;
        }
        $this->logInfo(static::LOG_DISPATCHING, [$order->getId()], static::LOG_CODE);

        $account = $this->getAccountService()->fetch($order->getAccountId());

        $order = $this->saveOrder(
            $order->setStatus(OrderStatus::DISPATCHING)
        );
        foreach ($order->getItems() as $item) {
            $item->setStatus(OrderStatus::DISPATCHING);
        }
        $this->getOrderItemClient()->saveCollection($order->getItems());

        $this->getOrderDispatcher()->generateJob($account, $order);
        $this->getStatsClient()->stat(
            sprintf(static::STAT_ORDER_DISPATCHED, $order->getChannel()),
            $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId()
        );
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
                if (! $orderException instanceof NotModifiedException) {
                    $throw = true;
                }
            }
        }

        if (isset($throw)) {
            throw $exception;
        }
    }

    public function archiveOrder(OrderEntity $order, $archive = true)
    {
        $order = $this->getOrderClient()->archive(
            $order->setArchived($archive)
        );
        $this->getStatsClient()->stat(
            sprintf(static::STAT_ORDER_ARCHIVED, $order->getChannel()),
            $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId()
        );
        return $order;
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

    public function cancelOrder(OrderEntity $order, $type, $reason)
    {
        $account = $this->getAccountService()->fetch($order->getAccountId());
        $status = OrderMapper::calculateOrderStatusFromCancelType($type);
        $cancel = $this->getCancelValue($order, $type, $reason);

        $order = $this->saveOrder(
            $order->setStatus($status)
        );
        foreach ($order->getItems() as $item) {
            $item->setStatus($status);
        }
        $this->getOrderItemClient()->saveCollection($order->getItems());

        $this->getOrderCanceller()->generateJob($account, $order, $cancel);
        $this->getStatsClient()->stat(
            sprintf(($type == Cancel::CANCEL_TYPE) ? static::STAT_ORDER_CANCELLED : static::STAT_ORDER_REFUNDED, $order->getChannel()),
            $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId()
        );
    }

    /**
     * @param OrderEntity $order
     * @param string $type
     * @param string $reason
     * @return CancelValue
     */
    protected function getCancelValue(OrderEntity $order, $type, $reason)
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[] = $this->getDi()->newInstance(CancelItem::class, [
                'orderItemId' => $item->getId(),
                'sku' => $item->getItemSku(),
                'quantity' => $item->getItemQuantity(),
                'amount' => $item->getIndividualItemPrice(),
                'unitPrice' => 0.00,
            ]);
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

    public function getCarriersData()
    {
        return $this->getCarriers()->getAllCarriers();
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

    protected function setShippingConversionService(ShippingConversionService $shippingConversionService)
    {
        $this->shippingConversionService = $shippingConversionService;
        return $this;
    }

    protected function getShippingConversionService()
    {
        return $this->shippingConversionService;
    }

    protected function getCarriers() {
        return $this->carriers;
    }

    protected function setCarriers(Carrier $carriers) {
        $this->carriers = $carriers;
        return $this;
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

    protected function setOrderItemClient(OrderItemClient $orderItemClient)
    {
        $this->orderItemClient = $orderItemClient;
        return $this;
    }

    protected function getOrderItemClient()
    {
        return $this->orderItemClient;
    }

    /**
     * @return self
     */
    protected function setFilterClient(FilterClient $filterClient)
    {
        $this->filterClient = $filterClient;
        return $this;
    }

    /**
     * @return FilterClient
     */
    protected function getFilterClient()
    {
        return $this->filterClient;
    }

    protected function getActionService()
    {
        return $this->actionService;
    }

    protected function setActionService(ActionService $actionService)
    {
        $this->actionService = $actionService;
        return $this;
    }
}
