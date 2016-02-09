<?php
namespace Orders\Order;

use CG\Account\Client\Service as AccountService;
use CG\Channel\Action\Order\MapInterface as ActionMapInterface;
use CG\Channel\Action\Order\Service as ActionService;
use CG\Channel\Carrier;
use CG\Channel\Gearman\Generator\Order\Cancel as OrderCanceller;
use CG\Channel\Gearman\Generator\Order\Dispatch as OrderDispatcher;
use CG\Channel\Type;
use CG\Http\Exception\Exception3xx\NotModified as NotModifiedException;
use CG\Http\SaveCollectionHandleErrorsTrait;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Order\Client\Collection as FilteredCollection;
use CG\Order\Client\Service as OrderClient;
use CG\Order\Service\Filter\StorageInterface as FilterClient;
use CG\Order\Service\Filter;
use CG\Order\Shared\Cancel\Item as CancelItem;
use CG\Order\Shared\Cancel\Value as Cancel;
use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Shared\Item\Entity as ItemEntity;
use CG\Order\Shared\Item\GiftWrap\Collection as GiftWrapCollection;
use CG\Order\Shared\Item\GiftWrap\Entity as GiftWrapEntity;
use CG\Order\Shared\Item\StorageInterface as OrderItemClient;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Shared\Note\Collection as OrderNoteCollection;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\Order\Shared\Status as OrderStatus;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\User\Service as UserService;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG_UI\View\Filters\Service as FilterService;
use CG_UI\View\Helper\DateFormat as DateFormatHelper;
use CG_UI\View\Table\Column\Collection as TableColumnCollection;
use CG_UI\View\Table\Column as TableColumn;
use CG_UI\View\Table\Row\Collection as TableRowCollection;
use CG_UI\View\Table;
use Exception;
use Orders\Order\Exception\MultiException;
use Orders\Order\Table\Row\Mapper as RowMapper;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Di\Di;
use Zend\Mvc\MvcEvent;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;
    use SaveCollectionHandleErrorsTrait;

    const ORDER_TABLE_COL_PREF_KEY = 'order-columns';
    const ORDER_TABLE_COL_POS_PREF_KEY = 'order-column-positions';
    const ORDER_SIDEBAR_STATE_KEY = 'order-sidebar-state';
    const ORDER_FILTER_BAR_STATE_KEY = 'order-filter-bar-state';
    const ACCOUNTS_PAGE = 1;
    const ACCOUNTS_LIMIT = 'all';
    const LOG_CODE = 'OrderModuleService';
    const LOG_UNDISPATCHABLE = 'Order %s has been flagged for dispatch but it is not in a dispatchable status (%s)';
    const LOG_DISPATCHING = 'Dispatching Order %s';
    const LOG_ALREADY_CANCELLED = '%s requested for Order %s but its already in status %s';
    const STAT_ORDER_ACTION_DISPATCHED = 'orderAction.dispatched.%s.%d.%d';
    const STAT_ORDER_ACTION_CANCELLED = 'orderAction.cancelled.%s.%d.%d';
    const STAT_ORDER_ACTION_REFUNDED = 'orderAction.refunded.%s.%d.%d';
    const STAT_ORDER_ACTION_ARCHIVED = 'orderAction.archived.%s.%d.%d';
    const STAT_ORDER_ACTION_TAGGED = 'orderAction.tagged.%s.%d.%d';
    const EVENT_ORDERS_DISPATCHED = 'Dispatched Orders';
    const EVENT_ORDER_CANCELLED = 'Refunded / Cancelled Orders';
    const MAX_SHIPPING_METHOD_LENGTH = 15;

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
    protected $intercomEventService;
    protected $rowMapper;
    protected $dateFormatHelper;

    protected $editableFulfilmentChannels = [OrderEntity::DEFAULT_FULFILMENT_CHANNEL => true];

    public function __construct(
        OrderClient $orderClient,
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
        ActionService $actionService,
        IntercomEventService $intercomEventService,
        RowMapper $rowMapper,
        DateFormatHelper $dateFormatHelper
    ) {
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
            ->setActionService($actionService)
            ->setIntercomEventService($intercomEventService)
            ->setRowMapper($rowMapper)
            ->setDateFormatHelper($dateFormatHelper);
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
        $orders = $this->getOrdersArrayWithTruncatedShipping($orders);
        $orders = $this->getOrdersArrayWithFormattedDates($orders);
        $orders = $this->getOrdersArrayWithGiftMessages($orderCollection, $orders);
        
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
                $order['channelImgUrl'] = $accountEntity->getImageUrl();
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

    protected function getOrdersArrayWithTruncatedShipping(array $orders)
    {
        $ellipsis = '...';
        $ellipsisLen = strlen($ellipsis);
        foreach ($orders as $index => $order) {
            if (strlen($order['shippingMethod']) <= (static::MAX_SHIPPING_METHOD_LENGTH + $ellipsisLen)) {
                continue;
            }
            $orders[$index]['shippingMethod'] = substr($order['shippingMethod'], 0, static::MAX_SHIPPING_METHOD_LENGTH) . $ellipsis;
        }
        return $orders;
    }

    protected function getOrdersArrayWithFormattedDates(array $orders)
    {
        $dateFormatter = $this->dateFormatHelper;
        foreach ($orders as $index => $order) {
            // Keep the dates in Y-m-d H:i:s, the Mustache template will change them to a human-friendly format
            $orders[$index]['purchaseDate'] = $dateFormatter($orders[$index]['purchaseDate'], StdlibDateTime::FORMAT);
            $orders[$index]['paymentDate'] = $dateFormatter($orders[$index]['paymentDate'], StdlibDateTime::FORMAT);
            $orders[$index]['printedDate'] = $dateFormatter($orders[$index]['printedDate'], StdlibDateTime::FORMAT);
            $orders[$index]['dispatchDate'] = $dateFormatter($orders[$index]['dispatchDate'], StdlibDateTime::FORMAT);
        }
        return $orders;
    }

    protected function getOrdersArrayWithGiftMessages(OrderCollection $orderCollection, array $orders)
    {
        foreach ($orders as $index => $order) {
            $orders[$index]['giftMessage'] = '';

            /** @var OrderEntity|null $orderEntity */
            $orderEntity = $orderCollection->getById($order['id']);
            if (!$orderEntity) {
                continue;
            }

            /** @var ItemEntity $orderItemEntity */
            foreach ($orderEntity->getItems() as $orderItemEntity) {
                /** @var GiftWrapCollection $giftWraps */
                $giftWraps = $orderItemEntity->getGiftWraps();
                $giftWraps->rewind();

                /** @var GiftWrapEntity|null $giftWrap */
                $giftWrap = $giftWraps->current();
                if (!$giftWrap) {
                    continue;
                }

                $orders[$index]['giftMessage'] = <<<GIFTWRAP
<div><strong>Gift Wrap:</strong> {$giftWrap->getGiftWrapType()}</div>
<div><strong>Gift Message:</strong> {$giftWrap->getGiftWrapMessage()}</div>
GIFTWRAP;

                continue 2;
            }
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

    public function setOrderClient(OrderClient $orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    /**
     * @return OrderClient
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

    public function isOrderEditable(OrderEntity $order)
    {
        return (isset($this->editableFulfilmentChannels[$order->getFulfilmentChannel()]));
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
        $columns = [
            ['name' => RowMapper::COLUMN_SKU,       'class' => ''],
            ['name' => RowMapper::COLUMN_PRODUCT,   'class' => ''],
            ['name' => RowMapper::COLUMN_QUANTITY,  'class' => 'quantity'],
            ['name' => RowMapper::COLUMN_PRICE,     'class' => 'price right'],
            ['name' => RowMapper::COLUMN_DISCOUNT,  'class' => 'price right'],
            ['name' => RowMapper::COLUMN_TOTAL,     'class' => 'price right'],
        ];

        $table = new Table();
        $tableColumns = new TableColumnCollection();
        foreach ($columns as $column) {
            $tableColumn = new TableColumn($column["name"], $column["class"]);
            $table->addColumn($tableColumn);
            $tableColumns->attach($tableColumn);
        }
        $tableRows = new TableRowCollection();
        $itemCount = 0;
        foreach ($order->getItems() as $item) {
            $toggleClass = (++$itemCount % 2 == 0 ? 'even' : 'odd');
            $className = 'item ' . $toggleClass;
            if (count($item->getGiftWraps())) {
                $className .= ' has-giftwrap';
            }
            $tableRow = $this->rowMapper->fromItem($item, $order, $tableColumns, $className);
            $tableRows->attach($tableRow);
            foreach ($item->getGiftWraps() as $giftWrap) {
                $tableRow = $this->rowMapper->fromGiftWrap($giftWrap, $order, $tableColumns, 'giftwrap ' . $toggleClass);
                $tableRows->attach($tableRow);
            }
        }

        if ($order->getTotalDiscount() || $order->getDiscountDescription()) {
            $tableRow = $this->rowMapper->fromOrderDiscount($order, $tableColumns, 'discount');
            $tableRows->attach($tableRow);
        }

        $table->setRows($tableRows);
        return $table;
    }

    protected function addOrderDiscount(Table $table, $discount, $discountDescription)
    {
        if ($discountDescription) {
            $discountDescription = "<b>Discount Summary</b><br />" . nl2br($discountDescription);
        }
        $cells = [
            $table->createCustomCell($discountDescription, null, 3),
            $table->createCustomCell('Order Discount:', null, 2),
            $table->createCustomCell($discount, 'right')
        ];
        $row = $table->createCustomRow($cells, 'discount');
        $table->setPostCustomRows([$row]);
    }

    public function getNamesFromOrderNotes(OrderNoteCollection $notes)
    {
        $dateFormatter = $this->dateFormatHelper;
        $itemNotes = array();
        foreach ($notes as $note) {
            $itemNote = $note->toArray();
            $itemNote["eTag"] = $note->getStoredETag();
            $itemNote["timestamp"] = $dateFormatter($itemNote["timestamp"]);
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

    public function patchOrders(OrderCollection $collection, $fields)
    {
        $this->getOrderClient()->patchCollection('orderIds', $collection->getIds(), $fields);
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

    public function tagOrdersByFilter($tag, Filter $filter)
    {
        // Use patching as its faster than saving the individual orders
        $this->orderClient->patchCollectionByFilterObject($filter, ['tag' => $tag], 'add');
    }

    /**
     * @deprecated Use tagOrdersByFilter()
     */
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
        $this->statsIncrement(
            static::STAT_ORDER_ACTION_TAGGED, [
                $order->getChannel(),
                $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId(),
                $this->getActiveUserContainer()->getActiveUser()->getId()
            ]
        );
    }

    public function unTagOrders($tag, OrderCollection $orders)
    {
        $exception = new MultiException();
        // Can't use patching for this as there's no way to remove an item from an array without knowing its index
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
        $this->notifyOfDispatch();

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

        $order = $this->setOrderToDispatching($order);

        $this->getOrderDispatcher()->generateJob($account, $order);
        $this->statsIncrement(
            static::STAT_ORDER_ACTION_DISPATCHED, [
                $order->getChannel(),
                $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId(),
                $this->getActiveUserContainer()->getActiveUser()->getId()
            ]
        );
    }

    protected function setOrderToDispatching(OrderEntity $order, $attempt = 1, $maxAttempts = 2)
    {
        try {
            $order = $this->saveOrder(
                $order->setStatus(OrderStatus::DISPATCHING)
            );
        } catch (Conflict $e) {
            if ($attempt >= $maxAttempts) {
                throw new \RuntimeException('We were unable to dispatch one or more orders, please try again. If the problem persists please contact support.');
            }
            $this->logDebug('Attempt %d to set Order %s status to dispatching conflicted, will re-fetch and retry', [$attempt, $order->getId()], static::LOG_CODE);
            $order = $this->orderClient->fetch($order->getId());
            return $this->setOrderToDispatching($order, ++$attempt);
        }
        foreach ($order->getItems() as $item) {
            $item->setStatus(OrderStatus::DISPATCHING);
        }
        $this->saveCollectionHandleErrors($this->orderItemClient, $order->getItems());
        return $order;
    }

    protected function reapplyChangesToEntityAfterConflict($fetchedEntity, $passedEntity)
    {
        $fetchedEntity->setStatus($passedEntity->getStatus());
        return $fetchedEntity;
    }

    protected function notifyOfDispatch()
    {
        $this->notifyIntercom(static::EVENT_ORDERS_DISPATCHED);
    }

    public function archiveOrdersByFilter(Filter $filter)
    {
        // Use patching as its faster than saving the individual orders
        $this->orderClient->patchCollectionByFilterObject($filter, ['archived' => true]);
    }

    /**
     * @deprecated Use tagOrdersByFilter()
     */
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
        $this->getOrderClient()->archive(
            $order->setArchived($archive)
        );
        $this->statsIncrement(
            static::STAT_ORDER_ACTION_ARCHIVED, [
                $order->getChannel(),
                $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId(),
                $this->getActiveUserContainer()->getActiveUser()->getId(),
            ]
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
        $this->notifyOfCancel();

        if (count($exception) > 0) {
            throw $exception;
        }
    }

    public function cancelOrder(OrderEntity $order, $type, $reason)
    {
        $account = $this->getAccountService()->fetch($order->getAccountId());
        $status = OrderMapper::calculateOrderStatusFromCancelType($type);
        if ($order->getStatus() == OrderStatus::getInActionWithCompletedStatuses()[$status]) {
            $this->logDebug(static::LOG_ALREADY_CANCELLED, [ucwords($type), $order->getId(), $order->getStatus()]);
            return;
        }
        $cancel = $this->getCancelValue($order, $type, $reason);

        $order = $this->saveOrder(
            $order->setStatus($status)
        );
        foreach ($order->getItems() as $item) {
            $item->setStatus($status);
        }
        $this->getOrderItemClient()->saveCollection($order->getItems());

        $this->getOrderCanceller()->generateJob($account, $order, $cancel);
        $this->statsIncrement(
            ($type == Cancel::CANCEL_TYPE) ? static::STAT_ORDER_ACTION_CANCELLED : static::STAT_ORDER_ACTION_REFUNDED, [
                $order->getChannel(),
                $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId(),
                $this->getActiveUserContainer()->getActiveUser()->getId()
            ]
        );
    }

    public function getRootOrganisationUnitForOrder(OrderEntity $order)
    {
        return $this->getOrganisationUnitService()->getRootOuFromOuId($order->getOrganisationUnitId());
    }

    protected function notifyOfCancel()
    {
        $this->notifyIntercom(static::EVENT_ORDER_CANCELLED);
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
                'timestamp' => date(StdlibDateTime::FORMAT),
                'reason' => $reason,
                'items' => $items,
                'shippingAmount' => $order->getShippingPrice(),
            ]
        );
    }

    protected function notifyIntercom($eventName)
    {
        $event = new IntercomEvent($eventName, $this->getActiveUser()->getId());
        $this->getIntercomEventService()->save($event);
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

    protected function getIntercomEventService()
    {
        return $this->intercomEventService;
    }

    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    protected function setRowMapper(RowMapper $rowMapper)
    {
        $this->rowMapper = $rowMapper;
        return $this;
    }

    protected function setDateFormatHelper(DateFormatHelper $dateFormatHelper)
    {
        $this->dateFormatHelper = $dateFormatHelper;
        return $this;
    }
}
