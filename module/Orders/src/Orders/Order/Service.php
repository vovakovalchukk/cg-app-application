<?php
namespace Orders\Order;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Amazon\Mcf\FulfillmentStatus\Filter as McfFulfillmentStatusFilter;
use CG\Amazon\Mcf\FulfillmentStatus\Status as McfFulfillmentStatus;
use CG\Amazon\Mcf\FulfillmentStatus\StorageInterface as McfFulfillmentStatusStorage;
use CG\Amazon\Order\FulfilmentChannel\Mapper as AmazonFulfilmentChannelMapper;
use CG\Channel\Action\Order\MapInterface as ActionMapInterface;
use CG\Channel\Action\Order\Service as ActionService;
use CG\Channel\Gearman\Generator\Order\Cancel as OrderCanceller;
use CG\Channel\Gearman\Generator\Order\Dispatch as OrderDispatcher;
use CG\Channel\Shipping\CourierTrackingUrl;
use CG\Channel\Type;
use CG\Http\Exception\Exception3xx\NotModified as NotModifiedException;
use CG\Http\SaveCollectionHandleErrorsTrait;
use CG\Image\Filter as ImageFilter;
use CG\Image\Service as ImageService;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Locale\EUVATCodeChecker;
use CG\Order\Client\Collection as FilteredCollection;
use CG\Order\Client\Service as OrderClient;
use CG\Order\Service\Filter;
use CG\Order\Service\Filter\StorageInterface as FilterClient;
use CG\Order\Shared\Cancel\Item as CancelItem;
use CG\Order\Shared\Cancel\Value as Cancel;
use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Shared\Item\Entity as ItemEntity;
use CG\Order\Shared\Item\GiftWrap\Collection as GiftWrapCollection;
use CG\Order\Shared\Item\GiftWrap\Entity as GiftWrapEntity;
use CG\Order\Shared\Item\StorageInterface as OrderItemClient;
use CG\Order\Shared\Label\Service as OrderLabelService;
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
use CG\User\Entity as User;
use CG\User\Service as UserService;
use CG_UI\View\Filters\Service as FilterService;
use CG_UI\View\Helper\DateFormat as DateFormatHelper;
use CG_UI\View\Table;
use CG_UI\View\Table\Column as TableColumn;
use CG_UI\View\Table\Column\Collection as TableColumnCollection;
use CG_UI\View\Table\Row\Collection as TableRowCollection;
use Exception;
use Orders\Order\Exception\MultiException;
use Orders\Order\Table\Row\Mapper as RowMapper;
use Orders\Order\TableService\OrdersTableUserPreferences;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Di\Di;
use Zend\Mvc\MvcEvent;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;
    use SaveCollectionHandleErrorsTrait;

    const ACCOUNTS_PAGE = 1;
    const ACCOUNTS_LIMIT = 'all';
    const LOG_CODE = 'OrderModuleService';
    const LOG_UNDISPATCHABLE = 'Order %s has been flagged for dispatch but it is not in a dispatchable status (%s)';
    const LOG_DISPATCHING = 'Dispatching Order %s';
    const LOG_UNPAYABLE = 'Order %s has been flagged for payment but it is not in a payable status (%s)';
    const LOG_PAYING = 'Paying for Order %s';
    const LOG_ALREADY_CANCELLED = '%s requested for Order %s but its already in status %s';
    const STAT_ORDER_ACTION_DISPATCHED = 'orderAction.dispatched.%s.%d.%d';
    const STAT_ORDER_ACTION_CANCELLED = 'orderAction.cancelled.%s.%d.%d';
    const STAT_ORDER_ACTION_REFUNDED = 'orderAction.refunded.%s.%d.%d';
    const STAT_ORDER_ACTION_ARCHIVED = 'orderAction.archived.%s.%d.%d';
    const STAT_ORDER_ACTION_TAGGED = 'orderAction.tagged.%s.%d.%d';
    const STAT_ORDER_ACTION_PAID = 'orderAction.paid.%s.%d.%d';
    const EVENT_ORDERS_DISPATCHED = 'Dispatched Orders';
    const EVENT_ORDER_CANCELLED = 'Refunded / Cancelled Orders';
    const MAX_SHIPPING_METHOD_LENGTH = 15;

    /** @var OrderClient $orderClient */
    protected $orderClient;
    /** @var OrderItemClient $orderItemClient */
    protected $orderItemClient;
    /** @var FilterClient $filterClient */
    protected $filterClient;
    /** @var FilterService $filterService */
    protected $filterService;
    /** @var UserService $userService */
    protected $userService;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var Di $di */
    protected $di;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var OrderDispatcher $orderDispatcher */
    protected $orderDispatcher;
    /** @var OrderCanceller $orderCanceller */
    protected $orderCanceller;
    /** @var ShippingConversionService $shippingConversionService */
    protected $shippingConversionService;
    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;
    /** @var ActionService $actionService */
    protected $actionService;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var RowMapper $rowMapper */
    protected $rowMapper;
    /** @var DateFormatHelper $dateFormatHelper */
    protected $dateFormatHelper;
    /** @var ImageService $imageService */
    protected $imageService;
    /** @var McfFulfillmentStatusStorage $mcfFulfillmentStatusStorage */
    protected $mcfFulfillmentStatusStorage;
    /** @var CourierTrackingUrl $courierTrackingUrl */
    protected $courierTrackingUrl;
    /** @var EUVATCodeChecker $euVatCodeChecker */
    protected $euVatCodeChecker;
    /** @var OrderLabelService $orderLabelService */
    protected $orderLabelService;
    /** @var OrdersTableUserPreferences $orderTableUserPreferences */
    protected $orderTableUserPreferences;

    protected $editableFulfilmentChannels = [OrderEntity::DEFAULT_FULFILMENT_CHANNEL => true];
    protected $editableBillingAddressFulfilmentChannels = [
        OrderEntity::DEFAULT_FULFILMENT_CHANNEL => true,
        AmazonFulfilmentChannelMapper::CG_FBA => true,
    ];
    protected $editableShippingAddressFulfilmentChannels = [
        OrderEntity::DEFAULT_FULFILMENT_CHANNEL => true,
    ];

    public function __construct(
        OrderClient $orderClient,
        OrderItemClient $orderItemClient,
        FilterClient $filterClient,
        FilterService $filterService,
        UserService $userService,
        ActiveUserInterface $activeUserContainer,
        Di $di,
        AccountService $accountService,
        OrderDispatcher $orderDispatcher,
        OrderCanceller $orderCanceller,
        ShippingConversionService $shippingConversionService,
        OrganisationUnitService $organisationUnitService,
        ActionService $actionService,
        IntercomEventService $intercomEventService,
        RowMapper $rowMapper,
        DateFormatHelper $dateFormatHelper,
        ImageService $imageService,
        McfFulfillmentStatusStorage $mcfFulfillmentStatusStorage,
        CourierTrackingUrl $courierTrackingUrl,
        EUVATCodeChecker $euVatCodeChecker,
        OrderLabelService $orderLabelService,
        OrdersTableUserPreferences $orderTableUserPreferences
    ) {
        $this->orderClient = $orderClient;
        $this->orderItemClient = $orderItemClient;
        $this->filterClient = $filterClient;
        $this->filterService = $filterService;
        $this->userService = $userService;
        $this->activeUserContainer = $activeUserContainer;
        $this->di = $di;
        $this->accountService = $accountService;
        $this->orderDispatcher = $orderDispatcher;
        $this->orderCanceller = $orderCanceller;
        $this->shippingConversionService = $shippingConversionService;
        $this->organisationUnitService = $organisationUnitService;
        $this->actionService = $actionService;
        $this->intercomEventService = $intercomEventService;
        $this->rowMapper = $rowMapper;
        $this->dateFormatHelper = $dateFormatHelper;
        $this->imageService = $imageService;
        $this->mcfFulfillmentStatusStorage = $mcfFulfillmentStatusStorage;
        $this->courierTrackingUrl = $courierTrackingUrl;
        $this->euVatCodeChecker = $euVatCodeChecker;
        $this->orderLabelService = $orderLabelService;
        $this->orderTableUserPreferences = $orderTableUserPreferences;
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
        $orders = $this->getOrdersArrayWithProductImage($orders);
        $orders = $this->getOrdersArrayWithTrackingUrl($orders);
        $orders = $this->getOrdersArrayWithLabelData($orders);

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
        $organisationUnit = $this->organisationUnitService
                                 ->fetch($this->activeUserContainer
                                              ->getActiveUserRootOrganisationUnitId()
            );

        foreach($orders as $index => $order) {
            $shippingAlias = $this->shippingConversionService
                                  ->fromMethodToAlias($order['shippingMethod'],
                                                      $organisationUnit
                );
            $orders[$index]['shippingMethod'] = $shippingAlias ? $shippingAlias->getName() : $orders[$index]['shippingMethod'];
        }
        return $orders;
    }

    public function getOrdersArrayWithAccountDetails(array $orders, MvcEvent $event)
    {
        $accounts = $this->accountService->fetchByOUAndStatus(
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

    /**
     * @return string
     */
    public function getStatusMessageForOrder($orderId, $status)
    {
        $statuses = $this->getStatusMessageForOrders([$orderId => $status]);
        return $statuses[$orderId];
    }

    /**
     * @return array Order ID => Status Message
     */
    public function getStatusMessageForOrders(array $orderStatuses)
    {
        $statusMessages = array_fill_keys(array_keys($orderStatuses), '');
        // We only need to do this for 'dispatch failed' orders
        $dispatchFailedOrderIds = [];
        foreach ($orderStatuses as $orderId => $status) {
            if ($status == OrderStatus::DISPATCH_FAILED) {
                $dispatchFailedOrderIds[] = $orderId;
            }
        }
        if (empty($dispatchFailedOrderIds)) {
            return $statusMessages;
        }

        $mcfFulfillmentStatuses = $this->getMcfFulfillmentStatusMessagesForOrders($dispatchFailedOrderIds);
        return array_merge($statusMessages, $mcfFulfillmentStatuses);
    }

    protected function getMcfFulfillmentStatusMessagesForOrders(array $orderIds)
    {
        $statusMessages = [];
        if (!$this->isAmazonMcfEnabled()) {
            return $statusMessages;
        }
        /**
         *  NOTE:
         *      If we ever want to retrieve fulfillment status information from
         *      another channel, refactor this to store order statuses in orderhub
         *      rather than in channel specific storages. Do not add other channels
         *      and layers of dependencies, a la dataplug. For reasons, ask Aaron.
         *       -- DO NOT ADD ANOTHER CHANNEL DEPENDENCY IN HERE --
         */
        try {
            $filter = (new McfFulfillmentStatusFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setOrderId($orderIds);
            $mcfFulfillmentStatuses = $this->mcfFulfillmentStatusStorage->fetchCollectionByFilter($filter);

            foreach ($mcfFulfillmentStatuses as $mcfFulfillmentStatusEntity) {
                if (in_array($mcfFulfillmentStatusEntity->getStatus(), McfFulfillmentStatus::getErrorStatuses())) {
                    $statusMessages[$mcfFulfillmentStatusEntity->getOrderId()] = $mcfFulfillmentStatusEntity->getError();
                }
            }
        } catch (NotFound $e) {
            // No-op
        }
        return $statusMessages;
    }

    protected function isAmazonMcfEnabled()
    {
        try {
            $filter = (new AccountFilter())
                ->setActive(true)
                ->setDeleted(false)
                ->setChannel(['amazon'])
                ->setOrganisationUnitId($this->getActiveUser()->getOuList());
            $amazonAccounts = $this->accountService->fetchByFilter($filter);
            foreach ($amazonAccounts as $account) {
                if ($account->getExternalData()['mcfEnabled']) {
                    return true;
                }
            }
            return false;

        } catch (NotFound $ex) {
            return false;
        }
    }

    protected function getOrdersArrayWithSanitisedStatus(array $orders)
    {
        $statuses = [];
        foreach ($orders as $orderArray) {
            $statuses[$orderArray['id']] = $orderArray['status'];
        }
        $statusMessages = $this->getStatusMessageForOrders($statuses);
        foreach ($orders as $index => $order) {
            $orders[$index]['status'] = str_replace(['_', '-'], ' ', $orders[$index]['status']);
            $orders[$index]['statusClass'] = str_replace(' ', '-', $orders[$index]['status']);
            $orders[$index]['message'] = $statusMessages[$orders[$index]['id']];
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
            $orders[$index]['emailDate'] = $dateFormatter($orders[$index]['emailDate'], StdlibDateTime::FORMAT);
        }
        return $orders;
    }

    protected function getOrdersArrayWithGiftMessages(OrderCollection $orderCollection, array $orders)
    {
        foreach ($orders as $index => $order) {
            $giftMessages = [];

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

                /** @var GiftWrapEntity $giftWrap */
                foreach ($giftWraps as $giftWrap) {
                    $giftMessages[] = [
                        'type' => $giftWrap->getGiftWrapType(),
                        'message' => $giftWrap->getGiftWrapMessage(),
                    ];
                }
            }

            $orders[$index]['giftMessageCount'] = count($giftMessages);
            $orders[$index]['giftMessages'] = json_encode($giftMessages);
        }

        return $orders;
    }

    protected function getOrdersArrayWithProductImage(array $orders)
    {
        $columns = $this->orderTableUserPreferences->fetchUserPrefOrderColumns();
        if (!isset($columns['image']) || filter_var($columns['image'], FILTER_VALIDATE_BOOLEAN) == false) {
            return $orders;
        }

        $imagesToFetch = [];
        foreach ($orders as $index => $order) {
            $orders[$index]['image'] = '';
            if (empty($order['items']) || empty($order['items'][0]['imageIds'])) {
                continue;
            }
            $imagesToFetch[$index] = $order['items'][0]['imageIds'][0];
        }
        if (empty($imagesToFetch)) {
            return $orders;
        }
        try {
            $images = $this->fetchImagesById(array_values($imagesToFetch));
        } catch (NotFound $e) {
            return $orders;
        }
        foreach ($imagesToFetch as $orderIndex => $imageId) {
            $image = $images->getById($imageId);
            if (!$image) {
                continue;
            }
            $orders[$orderIndex]['image'] = $image->getUrl();
        }

        return $orders;
    }

    protected function getOrdersArrayWithTrackingUrl(array $orders)
    {
        foreach ($orders as $index => $order) {
            foreach ($order['trackings'] as $i => $tracking) {
                $orders[$index]['trackings'][$i]['trackingUrl'] = $this->courierTrackingUrl->getTrackingUrl($tracking['carrier'], $tracking['number']);
            }
        }

        return $orders;
    }

    protected function getOrdersArrayWithLabelData(array $orders)
    {
        foreach ($orders as $index => $order) {
            $orders[$index]['labelCreatedDate'] = "";
        }
        return $orders;
    }

    protected function fetchImagesById(array $imageIds)
    {
        $filter = (new ImageFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($imageIds);
        return $this->imageService->fetchCollectionByPaginationAndFilters($filter);
    }

    public function getImagesForOrders(array $orderIds)
    {
        $imagesForOrders = [];
        $imagesToFetch = [];
        $filter = (new Filter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderIds);
        $orders = $this->getOrders($filter);
        foreach ($orders as $order) {
            $imagesForOrders[$order->getId()] = '';
            if (count($order->getItems()) == 0) {
                continue;
            }
            $order->getItems()->rewind();
            $item = $order->getItems()->current();
            if (empty($item->getImageIds())) {
                continue;
            }
            $imagesToFetch[$order->getId()] = $item->getImageIds()[0];
        }
        if (empty($imagesToFetch)) {
            return $imagesForOrders;
        }
        try {
            $images = $this->fetchImagesById(array_values($imagesToFetch));
        } catch (NotFound $e) {
            return $imagesForOrders;
        }
        foreach ($imagesToFetch as $orderId => $imageId) {
            $image = $images->getById($imageId);
            if (!$image) {
                continue;
            }
            $imagesForOrders[$orderId] = $image->getUrl();
        }
        return $imagesForOrders;
    }

    /**
     * @return User
     */
    public function getActiveUser()
    {
        return $this->activeUserContainer->getActiveUser();
    }

    /**
     * @param Filter $filter
     * @return OrderCollection
     */
    public function getOrders(Filter $filter)
    {
        return $this->orderClient->fetchCollectionByFilter($filter);
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

        $filter = $this->di->newInstance(
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

        $filter = $this->filterClient->save($filter);
        return $this->getOrdersFromFilterId($filter->getId(), $limit, $page, $orderBy, $orderDirection);
    }

    /**
     * @return OrderCollection
     */
    public function getPreviewOrder()
    {
        $filter = $this->di->newInstance(
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
        return $this->orderClient->fetchCollectionByFilterId(
            $filterId,
            $limit,
            $page,
            $orderBy,
            $orderDirection
        );
    }

    public function getOrder($orderId)
    {
        return $this->orderClient->fetch($orderId);
    }

    public function isOrderEditable(OrderEntity $order)
    {
        return (isset($this->editableFulfilmentChannels[$order->getFulfilmentChannel()]));
    }

    public function isBillingAddressEditable(OrderEntity $order)
    {
        return (isset($this->editableBillingAddressFulfilmentChannels[$order->getFulfilmentChannel()]));
    }

    public function isShippingAddressEditable(OrderEntity $order)
    {
        return (isset($this->editableShippingAddressFulfilmentChannels[$order->getFulfilmentChannel()]));
    }

    public function getOrderItemTable(OrderEntity $order)
    {
        $columns = [
            ['name' => RowMapper::COLUMN_SKU,       'class' => 'sku-col'],
            ['name' => RowMapper::COLUMN_PRODUCT,   'class' => 'product-name-col'],
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

    public function getCarrierPriorityOptions()
    {
        $frequentCarrierList = [
            'DPD',
            'Interlink',
            'MyHermes',
            'Royal Mail',
        ];

        $carrierDropdownOptions = [];
        foreach ($frequentCarrierList as $carrier) {
            $carrierDropdownOptions[] = [
                'title' => $carrier,
                'value' => $carrier,
            ];
        }
        return $carrierDropdownOptions;
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
            $users = $this->userService->fetchCollection("all", null, null, null, $userIds);
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
        return $this->orderClient->save($entity);
    }

    public function patchOrders(OrderCollection $collection, $fields)
    {
        $this->orderClient->patchCollection('orderIds', $collection->getIds(), $fields);
    }

    public function saveRecipientVatNumberToOrder(OrderEntity $order, $countryCode, $vatNumber)
    {
        $countryCode = str_replace(' ', '', $countryCode);
        $vatNumber = str_replace(' ', '', $vatNumber);
        $recipientVatNumber = $countryCode.$vatNumber;

        try {
            $logMsg = empty($recipientVatNumber) ? 'remove' : 'set';
            if (empty($recipientVatNumber) || $this->euVatCodeChecker->checkVat($countryCode, $vatNumber)) {
                $order = $this->saveOrder($order->setRecipientVatNumber($recipientVatNumber));
                $this->logDebug('Order %s successfully %s recipientVatNumber %s', [$order->getId(), $logMsg, $recipientVatNumber], static::LOG_CODE);
            }
        } catch (Conflict $e) {
            $this->logInfo('Conflict when attempting to %s Order %s recipientVatNumber to %s', [$order->getId(), $logMsg, $recipientVatNumber], static::LOG_CODE);
            throw $e;
        } catch (NotFound $e) {
            $this->logAlert('Could not find Order %s when attempting to %s recipientVatNumber to %s', [$order->getId(), $logMsg, $recipientVatNumber], static::LOG_CODE);
            throw $e;
        } catch (NotModifiedException $e) {
            $this->logDebug('Not modified Order %s when attempting to %s recipientVatNumber to %s', [$order->getId(), $logMsg, $recipientVatNumber], static::LOG_CODE);
        }
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
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                $this->activeUserContainer->getActiveUser()->getId()
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
        $actions = $this->actionService->getAvailableActionsForOrder($order);
        if (!array_key_exists(ActionMapInterface::DISPATCH, array_flip($actions))) {
            $this->logWarning(static::LOG_UNDISPATCHABLE, [$order->getId(), $order->getStatus()], static::LOG_CODE);
            return;
        }
        $this->logInfo(static::LOG_DISPATCHING, [$order->getId()], static::LOG_CODE);

        $account = $this->accountService->fetch($order->getAccountId());

        $order = $this->setOrderToDispatching($order);

        $this->orderDispatcher->generateJob($account, $order);
        $this->statsIncrement(
            static::STAT_ORDER_ACTION_DISPATCHED, [
                $order->getChannel(),
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                $this->activeUserContainer->getActiveUser()->getId()
            ]
        );
    }

    public function markOrdersAsPaid(OrderCollection $orders)
    {
        $exception = new MultiException();

        foreach ($orders as $order) {
            try {
                $this->markOrderAsPaid($order);
            } catch (Exception $orderException) {
                $exception->addOrderException($order->getId(), $orderException);
                $this->logException($orderException, 'error', __NAMESPACE__);
            }
        }

        if (count($exception) > 0) {
            throw $exception;
        }
    }

    public function markOrderAsPaid(OrderEntity $order)
    {
        $actions = $this->actionService->getAvailableActionsForOrder($order);
        if (!array_key_exists(ActionMapInterface::PAY, array_flip($actions))) {
            $this->logWarning(static::LOG_UNPAYABLE, [$order->getId(), $order->getStatus()], static::LOG_CODE);
            return;
        }
        $this->logInfo(static::LOG_PAYING, [$order->getId()], static::LOG_CODE);

        $order->setStatus(OrderStatus::NEW_ORDER);
        $order->setPaymentDate(date(StdlibDateTime::FORMAT));
        $order = $this->saveOrder($order);

        $this->statsIncrement(
            static::STAT_ORDER_ACTION_PAID, [
                $order->getChannel(),
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                $this->activeUserContainer->getActiveUser()->getId()
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

    public function archiveOrdersByFilter(Filter $filter, $archived = true)
    {
        // Use patching as its faster than saving the individual orders
        $this->orderClient->patchCollectionByFilterObject($filter, ['archived' => $archived]);
    }

    /**
     * @deprecated Use archiveOrdersByFilter()
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
        $this->orderClient->archive(
            $order->setArchived($archive)
        );
        $this->statsIncrement(
            static::STAT_ORDER_ACTION_ARCHIVED, [
                $order->getChannel(),
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                $this->activeUserContainer->getActiveUser()->getId(),
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
        $account = $this->accountService->fetch($order->getAccountId());
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
        $this->orderItemClient->saveCollection($order->getItems());

        $this->orderCanceller->generateJob($account, $order, $cancel);
        $this->statsIncrement(
            ($type == Cancel::CANCEL_TYPE) ? static::STAT_ORDER_ACTION_CANCELLED : static::STAT_ORDER_ACTION_REFUNDED, [
                $order->getChannel(),
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                $this->activeUserContainer->getActiveUser()->getId()
            ]
        );
    }

    public function getRootOrganisationUnitForOrder(OrderEntity $order)
    {
        return $this->organisationUnitService->getRootOuFromOuId($order->getOrganisationUnitId());
    }

    /**
     * @return \CG\OrganisationUnit\Entity
     */
    public function getVatOrganisationUnitForOrder(OrderEntity $order)
    {
        $ou = $this->organisationUnitService->fetch($order->getOrganisationUnitId());
        return $ou->getVatEntity();
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
            $items[] = $this->di->newInstance(CancelItem::class, [
                'orderItemId' => $item->getId(),
                'sku' => $item->getItemSku(),
                'quantity' => $item->getItemQuantity(),
                'amount' => $item->getIndividualItemPrice(),
                'unitPrice' => 0.00,
            ]);
        }

        return $this->di->newInstance(
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
        $this->intercomEventService->save($event);
    }
}
