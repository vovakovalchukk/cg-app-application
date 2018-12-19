<?php
namespace Orders\Order;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Amazon\Mcf\FulfillmentStatus\Filter as McfFulfillmentStatusFilter;
use CG\Amazon\Mcf\FulfillmentStatus\Status as McfFulfillmentStatus;
use CG\Amazon\Mcf\FulfillmentStatus\StorageInterface as McfFulfillmentStatusStorage;
use CG\Amazon\Order\FulfilmentChannel\Mapper as AmazonFulfilmentChannelMapper;
use CG\Channel\Action\Order\Service as ActionService;
use CG\Channel\Gearman\Generator\Order\Cancel as OrderCanceller;
use CG\Channel\Gearman\Generator\Order\Dispatch as OrderDispatcher;
use CG\FeatureFlags\Lookup\Service as FeatureFlagService;
use CG\Http\Exception\Exception3xx\NotModified as NotModifiedException;
use CG\Http\SaveCollectionHandleErrorsTrait;
use CG\Image\Filter as ImageFilter;
use CG\Image\Service as ImageService;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Locale\EUVATCodeChecker;
use CG\Order\Client\Action\Service as OrderActionService;
use CG\Order\Client\Service as OrderClient;
use CG\Order\Service\Filter;
use CG\Order\Service\Filter\StorageInterface as FilterClient;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Shared\Item\Entity as OrderItem;
use CG\Order\Shared\Item\StorageInterface as OrderItemClient;
use CG\Order\Shared\OrderLinker;
use CG\Order\Shared\Status as OrderStatus;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Client\Service as ProductService;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\LinkLeaf\Filter as ProductLinkLeafFilter;
use CG\Product\LinkLeaf\Service as ProductLinkLeafService;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\Table;
use CG_UI\View\Table\Column as TableColumn;
use CG_UI\View\Table\Column\Collection as TableColumnCollection;
use CG_UI\View\Table\Row\Collection as TableRowCollection;
use Exception;
use Orders\Order\Exception\MultiException;
use Orders\Order\Table\Row\Mapper as RowMapper;
use Zend\Di\Di;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;
    use SaveCollectionHandleErrorsTrait;

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

    /** @var OrderClient $orderClient */
    protected $orderClient;
    /** @var OrderItemClient $orderItemClient */
    protected $orderItemClient;
    /** @var FilterClient $filterClient */
    protected $filterClient;
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
    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;
    /** @var ActionService $actionService */
    protected $actionService;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var RowMapper $rowMapper */
    protected $rowMapper;
    /** @var ImageService $imageService */
    protected $imageService;
    /** @var McfFulfillmentStatusStorage $mcfFulfillmentStatusStorage */
    protected $mcfFulfillmentStatusStorage;
    /** @var EUVATCodeChecker $euVatCodeChecker */
    protected $euVatCodeChecker;
    /** @var OrderLinker */
    protected $orderLinker;
    /** @var  ProductLinkLeafService $productLinkLeafService */
    protected $productLinkLeafService;
    /** @var FeatureFlagService $featureFlagService */
    protected $featureFlagService;
    /** @var OrderActionService */
    protected $orderActionService;

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
        ActiveUserInterface $activeUserContainer,
        Di $di,
        AccountService $accountService,
        OrderDispatcher $orderDispatcher,
        OrderCanceller $orderCanceller,
        OrganisationUnitService $organisationUnitService,
        ActionService $actionService,
        IntercomEventService $intercomEventService,
        RowMapper $rowMapper,
        ImageService $imageService,
        McfFulfillmentStatusStorage $mcfFulfillmentStatusStorage,
        EUVATCodeChecker $euVatCodeChecker,
        OrderLinker $orderLinker,
        ProductLinkLeafService $productLinkLeafService,
        FeatureFlagService $featureFlagService,
        OrderActionService $orderActionService
    ) {
        $this->orderClient = $orderClient;
        $this->orderItemClient = $orderItemClient;
        $this->filterClient = $filterClient;
        $this->activeUserContainer = $activeUserContainer;
        $this->di = $di;
        $this->accountService = $accountService;
        $this->orderDispatcher = $orderDispatcher;
        $this->orderCanceller = $orderCanceller;
        $this->organisationUnitService = $organisationUnitService;
        $this->actionService = $actionService;
        $this->intercomEventService = $intercomEventService;
        $this->rowMapper = $rowMapper;
        $this->imageService = $imageService;
        $this->mcfFulfillmentStatusStorage = $mcfFulfillmentStatusStorage;
        $this->euVatCodeChecker = $euVatCodeChecker;
        $this->orderLinker = $orderLinker;
        $this->productLinkLeafService = $productLinkLeafService;
        $this->featureFlagService = $featureFlagService;
        $this->orderActionService = $orderActionService;
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

    public function fetchImagesById(array $imageIds)
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

    public function getLinkedOrdersData(OrderCollection $orders)
    {
        $expandedOrders = $this->orderLinker->expandOrderCollectionToIncludeLinkedOrders($orders);

        $linkedOrders = [];
        foreach ($orders as $order) {
            foreach ($order->getOrderLinks() as $orderLink) {
                foreach ($orderLink->getOrderIds() as $linkedOrderId) {
                    $linkedOrder = $expandedOrders->getById($linkedOrderId);
                    $linkedOrders[$order->getId()][] = [
                        'orderId' => $linkedOrderId,
                        'externalId' => $linkedOrder->getExternalId(),
                    ];
                }
            }
        }

        return $linkedOrders;
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

    public function getProductLinksForOrder(OrderEntity $order)
    {
        $productLinksBySku = [];
        $ou = $this->getRootOrganisationUnitForOrder($order);
        $orderItemSkus = $this->getFlatArrayOfItemSkus($order);

        if (count($orderItemSkus) == 0) {
            return $productLinksBySku;
        }

        $ouIdProductSkuList = $this->getOuIdProductSkuListFromOrder($orderItemSkus, $ou);
        $productLinkLeafFilter = (new ProductLinkLeafFilter('all', 1))->setOuIdProductSku($ouIdProductSkuList);

        try {
            $productLinks = $this->productLinkLeafService->fetchCollectionByFilter($productLinkLeafFilter);
        } catch (NotFound $exception) {
            return [];
        }

        foreach ($orderItemSkus as $orderItemSku) {
            $productLinksBySku[$orderItemSku] = $productLinks->getById(ProductLink::generateId($ou->getId(), $orderItemSku));
        }

        return $productLinksBySku;
    }

    protected function getFlatArrayOfItemSkus(OrderEntity $order)
    {
        $orderItemSkus = [];
        foreach ($order->getItems()->toArray() as $orderItem) {
            if (! empty($orderItem['itemSku'])) {
                $orderItemSkus[] = $orderItem['itemSku'];
            }
        }
        return $orderItemSkus;
    }

    protected function getOuIdProductSkuListFromOrder(array $orderItemSkus, $ou): array
    {
        return array_map(
            function($sku) use($ou) {
                return ProductLink::generateId($ou->getId(), $sku);
            },
            $orderItemSkus
        );
    }

    public function getOrderItemTable(OrderEntity $order)
    {
        $columns = $this->getOrderItemTableColumnsConfig($order);
        $productLinks = $this->getOrderItemTableProductLinks($order);

        $table = new Table();
        $tableColumns = $this->getOrderItemTableColumns($columns, $table);
        $tableRows = $this->getOrderItemTableRows($order, $tableColumns, $productLinks);
        $table->setRows($tableRows);
        return $table;
    }

    protected function getOrderItemTableColumnsConfig(OrderEntity $order): array
    {
        $columns = [
            ['name' => RowMapper::COLUMN_SKU,       'class' => 'sku-col'],
            ['name' => RowMapper::COLUMN_PRODUCT,   'class' => 'product-name-col'],
            ['name' => RowMapper::COLUMN_VARIATIONS,'class' => 'variation-attributes-col'],
            ['name' => RowMapper::COLUMN_QUANTITY,  'class' => 'quantity'],
            ['name' => RowMapper::COLUMN_PRICE,     'class' => 'price right'],
            ['name' => RowMapper::COLUMN_DISCOUNT,  'class' => 'price right'],
            ['name' => RowMapper::COLUMN_TOTAL,     'class' => 'price right'],
        ];

        if (!$this->doesOrderContainVariationAttributes($order)) {
            $columns = array_filter($columns, function(array $column)
            {
                return $column['name'] != RowMapper::COLUMN_VARIATIONS;
            });
        }

        return $columns;
    }

    protected function doesOrderContainVariationAttributes(OrderEntity $order): bool
    {
        $containsVariations = false;
        /** @var OrderItem $orderItem **/
        foreach ($order->getItems() as $orderItem) {
            if (!empty($orderItem->getItemVariationAttribute())) {
                $containsVariations = true;
                break;
            }
        }
        return $containsVariations;
    }

    protected function getOrderItemTableProductLinks(OrderEntity $order): array
    {
        $productLinks = [];
        if (
            $this->featureFlagService->featureEnabledForOu(
                ProductService::FEATURE_FLAG_LINKED_PRODUCTS,
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
            )
        ) {
            $productLinks = $this->getProductLinksForOrder($order);
        }
        return $productLinks;
    }

    protected function getOrderItemTableColumns(array $columnsConfig, Table $table): TableColumnCollection
    {
        $tableColumns = new TableColumnCollection();
        foreach ($columnsConfig as $column) {
            $tableColumn = new TableColumn($column["name"], $column["class"]);
            $table->addColumn($tableColumn);
            $tableColumns->attach($tableColumn);
        }
        return $tableColumns;
    }

    protected function getOrderItemTableRows(
        OrderEntity $order,
        TableColumnCollection $tableColumns,
        array $productLinks
    ): TableRowCollection {
        $tableRows = new TableRowCollection();
        $itemCount = 0;
        foreach ($order->getItems() as $item) {
            $toggleClass = (++$itemCount % 2 == 0 ? 'even' : 'odd');
            $className = 'item ' . $toggleClass;
            if (count($item->getGiftWraps())) {
                $className .= ' has-giftwrap';
            }
            $tableRow = $this->rowMapper->fromItem($item, $order, $tableColumns, $className, $productLinks);
            $tableRows->attach($tableRow);
            foreach ($item->getGiftWraps() as $giftWrap) {
                $tableRow = $this->rowMapper->fromGiftWrap($giftWrap, $order, $tableColumns, 'giftwrap ' . $toggleClass);
                $tableRows->attach($tableRow);
            }
            if (!isset($productLinks[$item->getItemSku()])) {
                continue;
            }
            $isFirstLinkedProduct = true;
            foreach ($productLinks[$item->getItemSku()]->getStockSkuMap() as $sku => $quantity) {
                $tableRow = $this->rowMapper->fromProductLink(
                    $sku,
                    $quantity,
                    'product-link-tr ' . $toggleClass,
                    $isFirstLinkedProduct
                );
                $isFirstLinkedProduct = false;
                $tableRows->attach($tableRow);
            }
        }

        if ($order->getTotalDiscount() || $order->getDiscountDescription()) {
            $tableRow = $this->rowMapper->fromOrderDiscount($order, $tableColumns, 'discount');
            $tableRows->attach($tableRow);
        }
        return $tableRows;
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

        /** @var OrderEntity $order */
        foreach ($orders as $order) {
            try {
                $this->orderActionService->dispatchOrder(
                    $order,
                    $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                    $this->activeUserContainer->getActiveUser()->getId()
                );
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

    public function markOrdersAsPaid(OrderCollection $orders)
    {
        $exception = new MultiException();

        /** @var OrderEntity $order */
        foreach ($orders as $order) {
            try {
                $this->orderActionService->markOrderAsPaid(
                    $order,
                    $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                    $this->activeUserContainer->getActiveUser()->getId()
                );
            } catch (Exception $orderException) {
                $exception->addOrderException($order->getId(), $orderException);
                $this->logException($orderException, 'error', __NAMESPACE__);
            }
        }

        if (count($exception) > 0) {
            throw $exception;
        }
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

        /** @var OrderEntity $order */
        foreach ($orders as $order) {
            try {
                $this->orderActionService->cancelOrder(
                    $order,
                    $type,
                    $reason,
                    $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                    $this->activeUserContainer->getActiveUser()->getId()
                );
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

    protected function notifyIntercom($eventName)
    {
        $event = new IntercomEvent($eventName, $this->getActiveUser()->getId());
        $this->intercomEventService->save($event);
    }
}
