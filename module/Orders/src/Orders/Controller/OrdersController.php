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

class OrdersController extends AbstractActionController
{


    protected $orderService;
    protected $filterService;
    protected $timelineService;
    protected $batchService;
    protected $bulkActionsService;
    protected $jsonModelFactory;
    protected $viewModelFactory;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        OrderService $orderService,
        FilterService $filterService,
        TimelineService $timelineService,
        BatchService $batchService,
        BulkActionsService $bulkActionsService)
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setOrderService($orderService)
            ->setFilterService($filterService)
            ->setTimelineService($timelineService)
            ->setBatchService($batchService)
            ->setBulkActionsService($bulkActionsService);
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

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();

        $ordersTable = $this->getOrderService()->getOrdersTable();
        $settings = $ordersTable->getVariable('settings');
        $settings->setSource($this->url()->fromRoute('Orders/ajax'));
        $view->addChild($ordersTable, 'ordersTable');

        $bulkActions = $this->getBulkActionsService()->getBulkActions();
        $bulkActions->addChild(
            $this->getViewModelFactory()->newInstance()->setTemplate('orders/orders/bulk-actions/index'),
            'afterActions'
        );
        $view->addChild($bulkActions, 'bulkItems');

        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getBatches(), 'batches');
        return $view;
    }

    public function orderAction()
    {
        $order = $this->getOrderService()->getOrder($this->params('order'));
        $view = $this->getViewModelFactory()->newInstance();

        $bulkActions = $this->getBulkActionsService()->getOrderBulkActions($order);
        $bulkActions->addChild(
            $this->getViewModelFactory()->newInstance()->setTemplate('orders/orders/bulk-actions/order'),
            'afterActions'
        );
        $view->addChild($bulkActions, 'bulkItems');

        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getNotes($order), 'notes');
        $view->addChild($this->getTimelineBoxes($order), 'timelineBoxes');
        $view->addChild($this->getOrderService()->getOrderItemTable($order), 'productPaymentTable');
        $view->setVariable('order', $order);
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
        $notes = $this->getViewModelFactory()->newInstance(["notes" => $itemNotes]);
        $notes->setTemplate('elements/notes');
        return $notes;
    }

    protected function getFilterBar()
    {
        $filterObject = $this->getFilterService()->getPersistentFilter();
        $viewRender = $this->getServiceLocator()->get('Mustache\View\Renderer');

        $filterRows = [];
        $filterRow = [];

        $dateRangeFilter = $this->getViewModelFactory()->newInstance();
        $dateRangeFilter->setTemplate('elements/date-range');
        $dateRangeFilter->setVariable(
            'options',
            // Example Data - Should be loaded via Service/Di
            include dirname(dirname(dirname(__DIR__))) . '/test/data/filterBar/daterange-options.php'
        );
        $filterRow[] = $viewRender->render($dateRangeFilter);

        $filterButtons = $this->getViewModelFactory()->newInstance();
        $filterButtons->setTemplate('elements/custom-select-group');
        $filterButtons->setVariable(
            'options',
            // Example Data - Should be loaded via Service/Di
            include dirname(dirname(dirname(__DIR__))) . '/test/data/filterBar/status-options.php'
        );
        $filterRow[] = $viewRender->render($filterButtons);

        $statusFilter = $this->getViewModelFactory()->newInstance();
        $statusFilter->setTemplate('elements/text');
        $statusFilter->setVariable(
            'options',
            // Example Data - Should be loaded via Service/Di
            include dirname(dirname(dirname(__DIR__))) . '/test/data/filterBar/search-options.php'
        );
        $filterRow[] = $viewRender->render($statusFilter);

        $filterButtons = $this->getViewModelFactory()->newInstance();
        $filterButtons->setTemplate('elements/buttons');
        $filterButtons->setVariable(
            'options',
            // Example Data - Should be loaded via Service/Di
            include dirname(dirname(dirname(__DIR__))) . '/test/data/filterBar/buttons.php'
        );
        $filterRow[] = $viewRender->render($filterButtons);
        $filterRows[] = $filterRow;

        $filterRow = [];
        $filterButtons = $this->getViewModelFactory()->newInstance();
        $filterButtons->setTemplate('elements/custom-select-group');
        $filterButtons->setVariable(
            'options',
            // Example Data - Should be loaded via Service/Di
            include dirname(dirname(dirname(__DIR__))) . '/test/data/filterBar/country-options.php'
        );
        $filterRow[] = $viewRender->render($filterButtons);
        $filterRows[] = $filterRow;

        $filterBar = $this->getViewModelFactory()->newInstance();
        $filterBar->setTemplate('layout/filters');
        $filterBar->setVariable('filterRows', $filterRows);

        return $filterBar;
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
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getAvailableOrganisationUnitIds());

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
            $data['iTotalRecords'] = (int) $orders->getTotal();
            $data['iTotalDisplayRecords'] = (int) $orders->getTotal();

            foreach ($orders as $order) {
                $data['Records'][] = $order->toArray();
            }
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function tagAction()
    {
        $tag = $this->params()->fromPost('tag');
        if (!$tag) {
            return $this->getJsonModelFactory()->newInstance(['tagged' => false]);
        }

        $ids = $this->params()->fromPost('orders');
        if (!is_array($ids) || empty($ids)) {
            return $this->getJsonModelFactory()->newInstance(['tagged' => false]);
        }

        $filter = $this->getFilterService()->getFilter()
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getAvailableOrganisationUnitIds())
            ->setId($ids);

        try {
            foreach($this->getOrderService()->getOrders($filter) as $order) {
                $tags = $order->getTags();
                $tags[] = $tag;
                try {
                    $this->getOrderService()->saveOrder($order->setTags(array_unique($tags)));
                } catch (NotModified $exception) {
                    // Not changed so ignore
                }
            }
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->getJsonModelFactory()->newInstance(['tagged' => true]);
    }
}