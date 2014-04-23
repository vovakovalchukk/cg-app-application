<?php
namespace Orders\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
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
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\Http\Rpc\Exception\BatchException as RpcBatchException;
use CG\Http\Rpc\Exception\Error\AbstractError as RpcError;
use Orders\Order\FilterService as FiltersService;
use Orders\Order\StoredFilters\Service as StoredFiltersService;

class OrdersController extends AbstractActionController
{
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

    protected function basePath()
    {
        $config = $this->getServiceLocator()->get('Config');
        if (isset($config['view_manager'], $config['view_manager']['base_path'])) {
            return $config['view_manager']['base_path'];
        }
        else {
            return $this->getServiceLocator()->get('Request')->getBasePath();
        }
    }

    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    public function getOrderService()
    {
        return $this->orderService;
    }

    public function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    public function getFilterService()
    {
        return $this->filterService;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    public function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    public function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    public function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    public function getBatchService()
    {
        return $this->batchService;

    }

    public function setTimelineService(TimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
        return $this;
    }

    public function getTimelineService()
    {
        return $this->timelineService;
    }

    public function setBulkActionsService(BulkActionsService $bulkActionsService)
    {
        $this->bulkActionsService = $bulkActionsService;
        return $this;
    }

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
        $view->addChild($this->getDetailsSidebar($view->getChildren()), 'sidebar');

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

    protected function getDetailsSidebar(array $children)
    {
        $sidebar = $this->getViewModelFactory()->newInstance();
        $sidebar->setTemplate('orders/orders/sidebar/navbar');

        $links = [];
        foreach ($children as $child) {
            $links[] = $this->viewModelVarNameToHTMLId($child->captureTo());
        }
        $sidebar->setVariable('links', $links);

        return $sidebar;
    }

    protected function viewModelVarNameToHTMLId($string)
    {
        return strtolower(implode("-", preg_split("/(?=[A-Z])/", $string)));
    }

    public function jsonAction()
    {
        $data = [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => [],
        ];

        $limit = 'all';
        $page = 1;
        if ($this->params()->fromPost('iDisplayLength') > 0) {
            $limit = $this->params()->fromPost('iDisplayLength');
            $page += $this->params()->fromPost('iDisplayStart') / $limit;
        }

        $filter = $this->getFilterService()->getFilter()
            ->setLimit($limit)
            ->setPage($page)
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getOuList());

        $orderByIndex = $this->params()->fromPost('iSortCol_0');
        if ($orderByIndex) {
            $orderBy = $this->params()->fromPost('mDataProp_'.$orderByIndex);
            $orderDirection = strtoupper($this->params()->fromPost('sSortDir_0', 'asc'));
            $filter->setOrderBy($orderBy)
                ->setOrderDirection($orderDirection);
        }

        $requestFilter = $this->params()->fromPost('filter', []);
        if (!empty($requestFilter)) {
            $filter = $this->getFilterService()->mergeFilters(
                $filter,
                $this->getFilterService()->getFilterFromArray($requestFilter)
            );
        }

        $this->getFilterService()->setPersistentFilter($filter);

        try {
            $orders = $this->getOrderService()->getOrders($filter);
            $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int) $orders->getTotal();

            foreach ($orders as $order) {
                $data['Records'][] = $order->toArray();
            }
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

    public function dispatchAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['dispatching' => false]);

        $ids = $this->params()->fromPost('orders');
        if (!is_array($ids) || empty($ids)) {
            return $response->setVariable('error', 'No Orders provided');
        }

        try {
            $this->getOrderService()->dispatchOrders($ids);
        } catch (RpcBatchException $batchException) {
            $requestedOrderIds = array_fill_keys($ids, true);

            $failedOrderIds = [];
            foreach ($batchException->getExceptions() as $exception) {
                if (!($exception instanceof RpcError)) {
                    continue;
                }

                $orderId = $exception->getRequestId();
                if (!isset($requestedOrderIds[$orderId])) {
                    continue;
                }

                $failedOrderIds[] = $orderId;
            }

            return $response->setVariable(
                'error',
                'Failed to mark the following orders for dispatch: ' . implode(', ', $failedOrderIds)
            );
        }

        return $response->setVariable('dispatching', true);
    }

    public function archiveAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['archived' => false]);

        $ids = $this->params()->fromPost('orders');
        if (!is_array($ids) || empty($ids)) {
            return $response->setVariable('error', 'No Orders provided');
        }

        $filter = $this->getFilterService()->getFilter()
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getOuList())
            ->setId($ids);

        try {
            foreach($this->getOrderService()->getOrders($filter) as $order) {
                try {
                    $this->getOrderService()->archiveOrder($order->setArchived(true));
                } catch (NotModified $exception) {
                    // Not changed so ignore
                }
            }
        } catch (NotFound $exception) {
            return $response->setVariable(
                'error',
                'Order' . (count($ids) > 1 ? 's' : '') . ' could not be found'
            );
        }

        return $response->setVariable('archived', true);
    }
}
