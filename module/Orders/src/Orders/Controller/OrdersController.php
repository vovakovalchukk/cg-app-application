<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Order\Service as OrderService;
use Orders\Filter\Service as FilterService;
use CG\Stdlib\Exception\Runtime\NotFound;

class OrdersController extends AbstractActionController
{
    protected $orderService;
    protected $filterService;
    protected $jsonModelFactory;
    protected $viewModelFactory;

    public function __construct(
        OrderService $orderService,
        FilterService $filterService,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory
    ) {
        $this
            ->setOrderService($orderService)
            ->setFilterService($filterService)
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
        $view = $this->getViewModelFactory()->newInstance();

        $ordersTable = $this->getOrderService()->getOrdersTable();
        $settings = $ordersTable->getVariable('settings');
        $settings->setSource($this->url()->fromRoute('Orders/ajax'));
        $view->addChild($ordersTable, 'ordersTable');

        $view->addChild($this->getBulkActions(), 'bulkItems');
        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getSidebar(), 'sidebar');
        $view->addChild($this->getNotes(), 'notes');
        $view->addChild($this->getTimelineBoxes(), 'timelineBoxes');

        return $view;
    }

    protected function getTimelineBoxes()
    {
        $data = [
            'timelineBoxes' => [
                [
                    'title' => 'Purchase Date',
                    'subtitle' => '17th Dec 2013',
                    'extraText' => '12:36pm',
                    'colour' => 'green'
                ],
                [
                    'title' => 'Payment Date',
                    'subtitle' => '17th Dec 2013',
                    'extraText' => '12:37pm',
                    'colour' => 'green'
                ],
                [
                    'title' => 'Invoice Created',
                    'subtitle' => '17th Dec 2013',
                    'extraText' => '12:38pm',
                    'colour' => 'green'
                ],
                [
                    'title' => 'Dispatch Pending',
                    'subtitle' => '17th Dec 2013',
                    'extraText' => '12:38pm',
                    'colour' => 'grey'
                ]
            ],
            'timelineTimes' => [
                ['status' => 'ok', 'time' => '3 Minutes'],
                ['status' => 'ok', 'time' => '3 Minutes'],
                ['status' => 'ok', 'time' => '5 Days'],
                ['status' => 'notice', 'time' => '']
            ],
            'timelineTotal' => '5d 0h 5m'
        ];
        $timelineBoxes = $this->getViewModelFactory()->newInstance($data);
        $timelineBoxes->setTemplate('elements/timeline-boxes');
        return $timelineBoxes;
    }

    protected function getNotes()
    {
        $notesData = [
            'notes' => [
                [
                    'author' => "James Higgins",
                    'date' => "17/12/2013 - 09:00:34",
                    'note' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit involuptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
                ],
                [
                    'author' => "Daniel Williams",
                    'date' => "17/12/2013 - 09:00:34",
                    'note' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit involuptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
                ],
                [
                    'author' => "Matt King",
                    'date' => "17/12/2013 - 09:00:34",
                    'note' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit involuptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
                ]
            ]
        ];
        $notes = $this->getViewModelFactory()->newInstance($notesData);
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
            [
                'bulkActions' => [
                    [
                        'title' => 'Invoice',
                        'class' => 'invoice',
                        'sub-actions' => [
                            ['title' => 'by SKU', 'action' => 'invoices-sku'],
                            ['title' => 'by Title', 'action' => 'invoices-title']
                        ]
                    ],
                    [
                        'title' => 'Dispatch',
                        'class' => 'dispatch'
                    ],
                    [
                        'title' => 'Tag / Untag',
                        'class' => 'tag-untag',
                        'sub-actions' => [

                        ]
                    ],
                    [
                        'title' => 'Download CSV',
                        'class' => 'download-csv'
                    ],
                    [
                        'title' => 'Courier',
                        'class' => 'courier',
                        'sub-actions' => [
                            ['title' => 'Create Royal Mail CSV', 'action' => 'royal-mail-csv']
                        ]
                    ],
                    [
                        'title' => 'Batch',
                        'class' => 'batch',
                        'sub-actions' => [
                            ['title' => 'Remove', 'action' => 'remove-from-batch']
                        ]
                    ],
                    [
                        'title' => 'Archive',
                        'class' => 'archive'
                    ]
                ]
            ]
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

        $dateFormat = 'd/m/y';
        $dateRangeOptions = [
            [
                'title' => 'All Time',
                'from' => 'All',
                'to' => 'All'
            ],
            [
                'title' => 'Today',
                'from' => date($dateFormat),
                'to' => date($dateFormat)
            ],
            [
                'title' => 'Last 7 days',
                'from' => date($dateFormat, strtotime("-7 days")),
                'to' => date($dateFormat)
            ],
            [
                'title' => 'Month to date',
                'from' => date($dateFormat, strtotime('first day of ' . date('F Y'))),
                'to' => date($dateFormat)
            ],
            [
                'title' => 'Year to date',
                'from' => date($dateFormat, strtotime('first day of January ' . date('Y'))),
                'to' => date($dateFormat)
            ],
            [
                'title' => 'The previous month',
                'from' => date($dateFormat, strtotime('first day of last month ')),
                'to' => date($dateFormat, strtotime('last day of last month ')),
            ]
        ];
        $dateRangeFilter = $this->getViewModelFactory()->newInstance();
        $dateRangeFilter->setTemplate('elements/date-range');
        $dateRangeFilter->setVariable('options', $dateRangeOptions);
        $filterRow[] = $viewRender->render($dateRangeFilter);

        $options = [
            'title' => "Status",
            'id' => 'filter-status',
            'options' => ['New', 'Processing', 'Dispatched']
        ];
        $filterButtons = $this->getViewModelFactory()->newInstance();
        $filterButtons->setTemplate('elements/custom-select-group');
        $filterButtons->setVariable('options', $options);
        $filterRow[] = $viewRender->render($filterButtons);

        $options = [
            'title' => 'Contains Text',
            'placeholder' => 'Contains Text...',
            'class' => '',
            'value' => $filterObject->getSearchTerm()
        ];
        $statusFilter = $this->getViewModelFactory()->newInstance();
        $statusFilter->setTemplate('elements/text');
        $statusFilter->setVariable('options', $options);
        $filterRow[] = $viewRender->render($statusFilter);

        $options = [
            ['value' => 'Apply Filters', 'name' => 'apply-filters', 'action' => 'apply-filters'],
            ['value' => 'Clear', 'name' => 'clear-filters', 'action' => 'clear-filters'],
            ['value' => 'Save', 'name' => 'save-filters', 'action' => 'save-filters'],
        ];
        $filterButtons = $this->getViewModelFactory()->newInstance();
        $filterButtons->setTemplate('elements/buttons');
        $filterButtons->setVariable('options', $options);
        $filterRow[] = $viewRender->render($filterButtons);
        $filterRows[] = $filterRow;

        $filterRow = [];
        $options = [
            'title' => 'Include Country',
            'options' => ['UK', 'Austria', 'Croatia', 'Cyprus', 'France', 'Germany', 'Italy', 'Spain'],
            'isOptional' => true
        ];
        $filterButtons = $this->getViewModelFactory()->newInstance();
        $filterButtons->setTemplate('elements/custom-select-group');
        $filterButtons->setVariable('options', $options);
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