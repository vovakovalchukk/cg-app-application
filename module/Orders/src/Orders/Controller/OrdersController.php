<?php
namespace Orders\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use Orders\Order\Exception\MultiException;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Order\Service as OrderService;
use Orders\Order\Batch\Service as BatchService;
use Orders\Order\Timeline\Service as TimelineService;
use Orders\Filter\Service as FilterService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Shared\Entity as OrderEntity;
use Orders\Order\BulkActions\Service as BulkActionsService;
use Orders\Module;
use DirectoryIterator;
use CG\Http\Rpc\Exception as RpcException;
use Orders\Order\FilterService as FiltersService;
use Orders\Order\StoredFilters\Service as StoredFiltersService;
use ArrayObject;
use CG\Stdlib\PageLimit;
use CG\Stdlib\OrderBy;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Zend\View\Model\JsonModel;
use CG\Order\Shared\Collection as OrderCollection;

class OrdersController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    protected $orderService;
    protected $filterService;
    protected $timelineService;
    protected $batchService;
    protected $bulkActionsService;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $filtersService;
    protected $storedFiltersService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        OrderService $orderService,
        FilterService $filterService,
        TimelineService $timelineService,
        BatchService $batchService,
        BulkActionsService $bulkActionsService,
        FiltersService $filtersService,
        StoredFiltersService $storedFiltersService
    )
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setOrderService($orderService)
            ->setFilterService($filterService)
            ->setTimelineService($timelineService)
            ->setBatchService($batchService)
            ->setBulkActionsService($bulkActionsService)
            ->setFiltersService($filtersService)
            ->setStoredFiltersService($storedFiltersService);
    }

    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
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

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    public function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    public function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    public function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    /**
     * @return BatchService
     */
    public function getBatchService()
    {
        return $this->batchService;

    }

    public function setTimelineService(TimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
        return $this;
    }

    /**
     * @return TimelineService
     */
    public function getTimelineService()
    {
        return $this->timelineService;
    }

    public function setBulkActionsService(BulkActionsService $bulkActionsService)
    {
        $this->bulkActionsService = $bulkActionsService;
        return $this;
    }

    /**
     * @return BulkActionsService
     */
    public function getBulkActionsService()
    {
        return $this->bulkActionsService;
    }

    public function setFiltersService(FiltersService $filtersService)
    {
        $this->filtersService = $filtersService;
        return $this;
    }

    /**
     * @return FiltersService
     */
    public function getFiltersService()
    {
        return $this->filtersService;
    }

    public function setStoredFiltersService(StoredFiltersService $storedFiltersService)
    {
        $this->storedFiltersService = $storedFiltersService;
        return $this;
    }

    /**
     * @return StoredFiltersService
     */
    public function getStoredFiltersService()
    {
        return $this->storedFiltersService;
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();

        $templateUrlMap = [];
        $webRoot = PROJECT_ROOT . '/public';
        $templates = new DirectoryIterator($webRoot . Module::PUBLIC_FOLDER . 'template/columns');
        foreach ($templates as $template) {
            if (!$template->isFile()) {
                continue;
            }
            $templateUrlMap[$template->getBasename('.html')]
                = $this->basePath() . str_replace($webRoot, '', $template->getPathname());
        }

        $ordersTable = $this->getOrderService()->getOrdersTable();
        $settings = $ordersTable->getVariable('settings');
        $settings->setSource($this->url()->fromRoute('Orders/ajax'));
        $settings->setTemplateUrlMap($templateUrlMap);
        $view->addChild($ordersTable, 'ordersTable');
        $bulkActions = $this->getBulkActionsService()->getBulkActions();
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
        $view->setVariable('filterNames', $this->getOrderService()->getFilterService()->getFilterNames());
        return $view;
    }

    protected function getStatusFilters()
    {
        $view = $this->getViewModelFactory()->newInstance(
            [
                'filters' => $this->getFiltersService()->getFilterConfig('stateFilters')
            ]
        );
        $view->setTemplate('orders/orders/sidebar/statusFilters');
        return $view;
    }

    public function orderAction()
    {
        $order = $this->getOrderService()->getOrder($this->params('order'));
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
        $view->addChild($bulkActions, 'bulkActions');
        $view->addChild($this->getTimelineBoxes($order), 'timelineBoxes');
        $view->addChild($this->getOrderService()->getOrderItemTable($order), 'productPaymentTable');
        $view->addChild($this->getNotes($order), 'notes');
        $view->addChild($this->getDetailsSidebar(), 'sidebar');
        $view->setVariable('subHeaderHide', true);
        return $view;
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
        $viewRender = $this->getServiceLocator()->get('Mustache\View\Renderer');
        $filterValues = $this->getFilterService()->getPersistentFilter();
        $filters = $this->getOrderService()->getFilterService()->getOrderFilters();
        return $filters->prepare($viewRender);
    }

    protected function getDetailsSidebar()
    {
        $sidebar = $this->getViewModelFactory()->newInstance();
        $sidebar->setTemplate('orders/orders/sidebar/navbar');

        $links = [
            'order-status' => 'Order Status',
            'bulk-actions' => 'Bulk Actions',
            'timeline-boxes' => 'Timeline',
            'order-alert' => 'Alert',
            'order-buyer-message' => 'Buyer Message',
            'addressInformation' => 'Address Information',
            'product-payment-table' => 'Payment Information',
            'notes' => 'Notes'

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

    protected function mergeOrderDataWithJsonData(ArrayObject $json, array $orderData)
    {
        $json['Records'] = $orderData['orders'];
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
        if (! isset($requestFilter['archived'])) {
            $requestFilter['archived'] = false;
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
                $data,
                $this->getOrderService()->getOrdersArrayWithAccountDetails($orders, $this->getEvent())
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

        ob_start();
        var_dump($filterId);
        $this->log('Requested Order Filter Id ' . trim(ob_get_clean()), 0, 'debug', __NAMESPACE__);

        try {
            $orders = $this->getOrderService()->getOrdersFromFilterId(
                $filterId,
                $pageLimit->getLimit(),
                $pageLimit->getPage(),
                $orderBy->getColumn(),
                $orderBy->getDirection()
            );

            $this->mergeOrderDataWithJsonData(
                $data,
                $this->getOrderService()->getOrdersArrayWithAccountDetails($orders, $this->getEvent())
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

    public function archiveAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['archived' => false]);

        try {
            $ids = $this->params()->fromPost('orders');
            if (!is_array($ids) || empty($ids)) {
                throw new NotFound();
            }

            $orders = $this->getOrderService()->getOrdersById($ids);
            return $this->archiveOrders($response, $orders);
        } catch (NotFound $exception) {
            return $response->setVariable('error', 'No Orders provided');
        }
    }

    public function archiveFilterIdAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['archived' => false]);

        try {
            $orders = $this->getOrderService()->getOrdersFromFilterId(
                $this->params()->fromRoute('filterId'),
                'all',
                1,
                null,
                null
            );

            return $this->archiveOrders($response, $orders);
        } catch (NotFound $exception) {
            return $response->setVariable('error', 'No Orders provided');
        }
    }

    protected function archiveOrders(JsonModel $response, OrderCollection $orders)
    {
        try {
            $this->getOrderService()->archiveOrders($orders);
        } catch (MultiException $exception) {
            $failedOrderIds = [];
            foreach ($exception as $orderId => $orderException) {
                $failedOrderIds[] = $orderId;
            }

            return $response->setVariable(
                'error',
                'Failed to mark the following orders as archived: ' . implode(', ', $failedOrderIds)
            );
        }

        return $response->setVariable('archived', true);
    }
}
