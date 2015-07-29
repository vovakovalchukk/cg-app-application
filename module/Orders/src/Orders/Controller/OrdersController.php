<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Filters\Service as UIFiltersService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Order\Service as OrderService;
use Orders\Order\Batch\Service as BatchService;
use Orders\Order\Timeline\Service as TimelineService;
use Orders\Filter\Service as FilterService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Shared\Entity as OrderEntity;
use Orders\Order\BulkActions\Service as BulkActionsService;
use Orders\Order\StoredFilters\Service as StoredFiltersService;
use ArrayObject;
use CG\Stdlib\PageLimit;
use CG\Stdlib\OrderBy;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_Usage\Service as UsageService;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;

class OrdersController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const FILTER_SHIPPING_METHOD_NAME = "shippingMethod";
    const FILTER_SHIPPING_ALIAS_NAME = "shippingAliasId";
    const FILTER_TYPE = "orders";

    protected $orderService;
    protected $filterService;
    protected $timelineService;
    protected $batchService;
    protected $bulkActionsService;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $uiFiltersService;
    protected $storedFiltersService;
    protected $usageService;
    protected $shippingConversionService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        OrderService $orderService,
        FilterService $filterService,
        TimelineService $timelineService,
        BatchService $batchService,
        BulkActionsService $bulkActionsService,
        UIFiltersService $uiFiltersService,
        StoredFiltersService $storedFiltersService,
        UsageService $usageService,
        ShippingConversionService $shippingConversionService
    )
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setOrderService($orderService)
            ->setFilterService($filterService)
            ->setTimelineService($timelineService)
            ->setBatchService($batchService)
            ->setBulkActionsService($bulkActionsService)
            ->setUIFiltersService($uiFiltersService)
            ->setStoredFiltersService($storedFiltersService)
            ->setUsageService($usageService)
            ->setShippingConversionService($shippingConversionService);
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $ordersTable = $this->getOrderService()->getOrdersTable();

        $ordersTable->setVariable('filterValues',
            $this->getFilterService()->getMapper()->toArray(
                $this->getFilterService()->getPersistentFilter()
            )
        );
        $settings = $ordersTable->getVariable('settings');
        $settings->setSource($this->url()->fromRoute('Orders/ajax'));
        $settings->setTemplateUrlMap($this->mustacheTemplateMap('orderList'));
        $view->addChild($ordersTable, 'ordersTable');
        $bulkActions = $this->getBulkActionsViewModel();
        $bulkAction = $this->getViewModelFactory()->newInstance()->setTemplate('orders/orders/bulk-actions/index');
        $bulkAction->setVariable('isHeaderBarVisible', $this->getOrderService()->isFilterBarVisible());
        $bulkActions->addChild(
            $bulkAction,
            'afterActions'
        );

        $view->addChild($bulkActions, 'bulkItems');
        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getStatusFilters(), 'statusFiltersSidebar');
        $view->addChild(
            $this->getStoredFiltersService()->getStoredFiltersSidebarView(
                $this->getOrderService()->getActiveUserPreference()
            ),
            'storedFiltersSidebar'
        );
        $view->addChild($this->getBatches(), 'batches');
        $view->setVariable('isSidebarVisible', $this->getOrderService()->isSidebarVisible());
        $view->setVariable('isHeaderBarVisible', $this->getOrderService()->isFilterBarVisible());
        $view->setVariable('filterNames', $this->getUIFiltersService()->getFilterNames(static::FILTER_TYPE));
        return $view;
    }

    protected function getStatusFilters()
    {
        $view = $this->getViewModelFactory()->newInstance(
            [
                'filters' => $this->getUIFiltersService()->getFilterConfig('stateFilters')
            ]
        );
        $view->setTemplate('orders/orders/sidebar/statusFilters');
        return $view;
    }

    protected function getBulkActionsViewModel()
    {
        $bulkActionsViewModel = $this->getBulkActionsService()->getBulkActions();
        if(!$this->getUsageService()->hasUsageBeenExceeded()) {
            return $bulkActionsViewModel;
        }

        $actions = $bulkActionsViewModel->getActions();
        foreach($actions as $action) {
            $action->setEnabled(false);
        }

        return $bulkActionsViewModel;
    }

    public function orderAction()
    {
        if ($this->getUsageService()->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }

        $order = $this->getOrderService()->getOrder($this->params('order'));
        $carriers = $this->getCarrierSelect();
        $view = $this->getViewModelFactory()->newInstance(
            [
                'order' => $order
            ]
        );
        $bulkActions = $this->getBulkActionsService()->getOrderBulkActions($order);
        $bulkActions->addChild(
            $this->getViewModelFactory()->newInstance()->setTemplate('orders/orders/bulk-actions/order'),
            'afterActions'
        );
        $statusTemplate = $this->getStatus($order->getStatus());

        $view->addChild($statusTemplate, 'status');
        $view->addChild($bulkActions, 'bulkActions');
        $view->addChild($this->getTimelineBoxes($order), 'timelineBoxes');
        $view->addChild($this->getOrderService()->getOrderItemTable($order), 'productPaymentTable');
        $view->addChild($this->getNotes($order), 'notes');
        $view->addChild($this->getDetailsSidebar(), 'sidebar');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        $view->setVariable('carriers', $carriers);
        $view->addChild($this->getCarrierSelect(), 'carrierSelect');
        $view->setVariable('editable', $this->getOrderService()->isOrderEditable($order));
        $view->setVariable('rootOu', $this->getOrderService()->getRootOrganisationUnitForOrder($order));
        return $view;
    }

    protected function getStatus($statusText)
    {
        $status = $this->getViewModelFactory()->newInstance();
        $status->setTemplate("columns/status.mustache");
        $status->setVariable('status', $statusText);
        $status->setVariable('statusClass', str_replace(' ', '-', $statusText));

        return $status;
    }

    protected function getCarrierSelect()
    {
        $order = $this->getOrderService()->getOrder($this->params('order'));
        $carriers = $this->getOrderService()->getCarriersData();
        $trackings = $order->getTrackings();
        $trackings->rewind();
        $tracking = $trackings->current();
        $options = [];
        foreach ($carriers as $carrier) {
            $selected = false;
            if(!is_null($tracking)) {
                $selected = ($tracking->getCarrier() == $carrier);
            }
            $options[] = [
                'title' => $carrier,
                'value' => $carrier,
                'selected' => $selected
            ];
        }
        $carrierSelect = $this->getViewModelFactory()->newInstance(["options" => $options]);
        $carrierSelect->setTemplate("elements/custom-select.mustache");
        $carrierSelect->setVariable("name", "carrier");
        $carrierSelect->setVariable("id", "carrier");
        $carrierSelect->setVariable("blankOption", true);
        return $carrierSelect;
    }

    protected function getBatches()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('layout/sidebar/batches');
        $view->setVariable('batches', $this->getBatchService()->getBatches());
        return $view;
    }

    protected function getTimelineBoxes(OrderEntity $order)
    {
        $timelineBoxes = $this->getViewModelFactory()->newInstance(
            $this->getTimelineService()->getTimeline($order)
        );
        $timelineBoxes->setTemplate('elements/timeline-boxes');
        return $timelineBoxes;
    }

    protected function getNotes(OrderEntity $order)
    {
        $itemNotes = $this->getOrderService()->getNamesFromOrderNotes($order->getNotes());
        $notes = $this->getViewModelFactory()->newInstance(["notes" => $itemNotes, "order" => $order]);
        $notes->setTemplate('elements/notes');
        return $notes;
    }

    protected function getFilterBar()
    {
        $filterValues = $this->getFilterService()->getPersistentFilter();
        $filters = $this->getUIFiltersService()->getFilters(static::FILTER_TYPE, $filterValues);
        return $filters->prepare();
    }

    protected function getDetailsSidebar()
    {
        $sidebar = $this->getViewModelFactory()->newInstance();
        $sidebar->setTemplate('orders/orders/sidebar/navbar');

        $links = [
            'timeline' => 'Timeline',
            'order-alert' => 'Alert',
            'order-buyer-message' => 'Buyer Message',
            'addressInformation' => 'Address Information',
            'tracking-information' => 'Shipping',
            'product-payment-table' => 'Payment Information',
            'order-notes' => 'Notes'

        ];
        $sidebar->setVariable('links', $links);

        return $sidebar;
    }

    protected function getDefaultJsonData()
    {
        return new ArrayObject(
            [
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'sEcho' => (int) $this->params()->fromPost('sEcho'),
                'Records' => [],
                'sFilterId' => null,
            ]
        );
    }

    protected function mergeOrderDataWithJsonData(PageLimit $pageLimit, ArrayObject $json, array $orderData)
    {
        $json['Records'] = $pageLimit->getPageData($orderData['orders']);
        $json['iTotalRecords'] = $json['iTotalDisplayRecords'] = $orderData['orderTotal'];
        $json['sFilterId'] = $orderData['filterId'];
        return $this;
    }

    protected function getPageLimit()
    {
        $pageLimit = new PageLimit();

        if ($this->params()->fromPost('iDisplayLength') > 0) {
            $pageLimit
                ->setLimit($this->params()->fromPost('iDisplayLength'))
                ->setPageFromOffset($this->params()->fromPost('iDisplayStart'));
        }

        return $pageLimit;
    }

    protected function getOrderBy()
    {
        $orderBy = new OrderBy();

        $orderByIndex = $this->params()->fromPost('iSortCol_0');
        if ($orderByIndex) {
            $orderBy
                ->setColumn($this->params()->fromPost('mDataProp_' . $orderByIndex))
                ->setDirection($this->params()->fromPost('sSortDir_0', 'asc'));
        }

        return $orderBy;
    }

    public function jsonFilterAction()
    {
        $data = $this->getDefaultJsonData();
        $pageLimit = $this->getPageLimit();
        $orderBy = $this->getOrderBy();

        $filter = $this->getFilterService()->getFilter()
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getOuList())
            ->setPage($pageLimit->getPage())
            ->setLimit($pageLimit->getLimit())
            ->setOrderBy($orderBy->getColumn())
            ->setOrderDirection($orderBy->getDirection());

        $requestFilter = $this->params()->fromPost('filter', []);
        if (!isset($requestFilter['archived'])) {
            $requestFilter['archived'] = [false];
        }

        $requestFilter['hasItems'] = [true];

        if (isset($requestFilter[static::FILTER_SHIPPING_ALIAS_NAME])) {
            $methodNames = $this->getShippingConversionService()->fromAliasIdsToMethodNames($requestFilter[static::FILTER_SHIPPING_ALIAS_NAME]);
            $requestFilter[static::FILTER_SHIPPING_METHOD_NAME] = $methodNames;
        }

        if (!empty($requestFilter)) {
            $filter = $this->getFilterService()->mergeFilters(
                $filter,
                $this->getFilterService()->getFilterFromArray($requestFilter)
            );
        }

        $this->getFilterService()->setPersistentFilter($filter);

        try {
            $orders = $this->getOrderService()->getOrders($filter);
            $this->mergeOrderDataWithJsonData(
                $pageLimit,
                $data,
                $this->getOrderService()->alterOrderTable($orders, $this->getEvent())
            );
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function jsonFilterIdAction()
    {
        $data = $this->getDefaultJsonData();
        $pageLimit = $this->getPageLimit();
        $orderBy = $this->getOrderBy();
        $filterId = $this->params()->fromRoute('filterId');

        $this->logDebugDump($filterId, "Filter id: ");

        try {
            $orders = $this->getOrderService()->getOrdersFromFilterId(
                $filterId,
                $pageLimit->getLimit(),
                $pageLimit->getPage(),
                $orderBy->getColumn(),
                $orderBy->getDirection()
            );

            $this->mergeOrderDataWithJsonData(
                $pageLimit,
                $data,
                $this->getOrderService()->alterOrderTable($orders, $this->getEvent())
            );
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function updateColumnsAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['updated' => false]);

        $updatedColumns = $this->params()->fromPost('columns');
        if (!$updatedColumns) {
            return $response->setVariable('error', 'No columns provided');
        }

        $this->getOrderService()->updateUserPrefOrderColumns($updatedColumns);

        return $response->setVariable('updated', true);
    }

    public function updateColumnOrderAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['updated' => false]);
        $this->updateColumnPositions();
        return $response->setVariable('updated', true);
    }

    protected function updateColumnPositions()
    {
        $keyPrefix = 'mDataProp_';
        $columnPositions = [];
        $post = $this->params()->fromPost();
        foreach ($post as $key => $value) {
            if (strpos($key, $keyPrefix) === 0) {
                $columnPositions[$value] = substr($key, strlen($keyPrefix));
            }
        }

        $this->getOrderService()->updateUserPrefOrderColumnPositions($columnPositions);
    }

    protected function setUsageService(UsageService $usageService)
    {
        $this->usageService = $usageService;
        return $this;
    }

    protected function getUsageService()
    {
        return $this->usageService;
    }

    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->orderService;
    }

    protected function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    /**
     * @return FilterService
     */
    protected function getFilterService()
    {
        return $this->filterService;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    protected function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    /**
     * @return BatchService
     */
    protected function getBatchService()
    {
        return $this->batchService;

    }

    protected function setTimelineService(TimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
        return $this;
    }

    /**
     * @return TimelineService
     */
    protected function getTimelineService()
    {
        return $this->timelineService;
    }

    protected function setBulkActionsService(BulkActionsService $bulkActionsService)
    {
        $this->bulkActionsService = $bulkActionsService;
        return $this;
    }

    /**
     * @return BulkActionsService
     */
    protected function getBulkActionsService()
    {
        return $this->bulkActionsService;
    }

    protected function setUIFiltersService(UIFiltersService $uiFiltersService)
    {
        $this->uiFiltersService = $uiFiltersService;
        return $this;
    }

    /**
     * @return UIFiltersService
     */
    protected function getUIFiltersService()
    {
        return $this->uiFiltersService;
    }

    protected function setStoredFiltersService(StoredFiltersService $storedFiltersService)
    {
        $this->storedFiltersService = $storedFiltersService;
        return $this;
    }

    /**
      @return StoredFiltersService
    */
    protected function getStoredFiltersService()
    {
        return $this->storedFiltersService;
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
}
