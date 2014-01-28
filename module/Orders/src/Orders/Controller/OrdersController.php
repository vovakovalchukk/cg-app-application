<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Order\Service as OrderService;
use Orders\Order\Timeline\Service as TimelineService;
use Orders\Filter\Service as FilterService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Shared\Entity as OrderEntity;

class OrdersController extends AbstractActionController
{
    protected $orderService;
    protected $filterService;
    protected $timelineService;
    protected $jsonModelFactory;
    protected $viewModelFactory;

    public function __construct(
        OrderService $orderService,
        FilterService $filterService,
        TimelineService $timelineService,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory
    ) {
        $this
            ->setOrderService($orderService)
            ->setFilterService($filterService)
            ->setTimelineService($timelineService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory);
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

    public function setTimelineService(TimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
        return $this;
    }

    public function getTimelineService()
    {
        return $this->timelineService;
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();

        $ordersTable = $this->getOrderService()->getOrdersTable();
        $settings = $ordersTable->getVariable('settings');
        $settings->setSource($this->url()->fromRoute('Orders/ajax'));
        $view->addChild($ordersTable, 'ordersTable');

        $view->addChild($this->getBulkActions(), 'bulkItems');
        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getSidebar(), 'sidebar');
        return $view;
    }

    public function orderAction()
    {
        $order = $this->getOrderService()->getOrder($this->params('order'));
        $view = $this->getViewModelFactory()->newInstance();

        $view->addChild($this->getBulkActions(), 'bulkItems');
        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getSidebar(), 'sidebar');
        $view->addChild($this->getNotes(), 'notes');

        $view->addChild($this->getTimelineBoxes($order), 'timelineBoxes');
        $view->setVariable('order', $order);
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

    protected function getNotes()
    {
        $notes = $this->getViewModelFactory()->newInstance(
            // Example Data - Should be loaded via Service/Di
            include dirname(dirname(dirname(__DIR__))) . '/test/data/notes.php'
        );
        $notes->setTemplate('elements/notes');
        return $notes;
    }

    protected function getSidebar()
    {
        $sidebar = $this->getViewModelFactory()->newInstance();
        $sidebar->setTemplate('orders/orders/sidebar');
        return $sidebar;
    }

    protected function getBulkActions()
    {
        $bulkItems = $this->getViewModelFactory()->newInstance(
            // Example Data - Should be loaded via Service/Di
            include dirname(dirname(dirname(__DIR__))) . '/test/data/bulkactions.php'
        );
        $bulkItems->setTemplate('layout/bulk-actions');
        return $bulkItems;
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
            ->setOrganisationUnitId([$this->getOrderService()->getActiveUser()->getOrganisationUnitId()]);

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
}