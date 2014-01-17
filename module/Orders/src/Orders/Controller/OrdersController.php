<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\DataTable;

class OrdersController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $ordersTable;

    public function __construct(JsonModelFactory $jsonModelFactory, ViewModelFactory $viewModelFactory, DataTable $ordersTable)
    {
        $this
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setOrdersTable($ordersTable);
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

    public function setOrdersTable(DataTable $ordersTable)
    {
        $this->ordersTable = $ordersTable;
        return $this;
    }

    public function getOrdersTable()
    {
        return $this->ordersTable;
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();

        $ordersTable = $this->getOrdersTable();
        $settings = $ordersTable->getVariable('settings');
        $settings->setSource($this->url()->fromRoute('Orders/ajax'));
        $view->addChild($ordersTable, 'ordersTable');

        $view->addChild($this->getBulkActions(), 'bulkItems');
        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getSidebar(), 'sidebar');
        return $view;
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
                        'title' => 'Print',
                        'class' => 'print',
                        'sub-actions' => [
                            ['title' => 'Invoices',         'action' => 'invoices'],
                            ['title' => 'Invoices by SKU',  'action' => 'invoices-sku'],
                            ['title' => 'Invoices by Title','action' => 'invoices-title'],
                            ['title' => 'Picking List',     'action' => 'picking-list'],
                            ['title' => 'Thermal Print',    'action' => 'thermal-print']
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
                            ['title' => 'Example 1',         'action' => 'example'],
                            ['title' => 'Example 2',         'action' => 'example']
                        ]
                    ],
                    [
                        'title' => 'Download',
                        'class' => 'download',
                        'sub-actions' => [
                            ['title' => 'Basic VAT Report', 'action' => 'vat-report']
                        ]
                    ],
                    [
                        'title' => 'Courier',
                        'class' => 'courier',
                        'sub-actions' => [
                            ['title' => 'Create Royal Mail CSV',      'action' => 'add-tracking-numbers'],
                            ['title' => 'Create Royal Mail CSV',      'action' => 'royal-mail-csv'],
                            ['title' => 'Fulfillment by Amazon',      'action' => 'fulfillment-by-amazon'],
                            ['title' => 'Print DPD Labels',           'action' => 'print-dpd-labels'],
                            ['title' => 'Print eParcel Label',        'action' => 'print-eparcel-labels'],
                            ['title' => 'Print Interlink Labels',     'action' => 'print-interlink-labels'],
                            ['title' => 'Print ParcelForce Labels',   'action' => 'print-parcelforce-labels'],
                            ['title' => 'Print UPS Labels',           'action' => 'print-ups-labels'],
                            ['title' => 'Print TNT Labels',           'action' => 'print-tnt-labels'],
                            ['title' => 'Print Yodel Labels',         'action' => 'print-yodel-labels'],
                            ['title' => 'UKMail Export',              'action' => 'ukmail-export']
                        ]
                    ],
                    [
                        'title' => 'Batch',
                        'class' => 'batch',
                        'sub-actions' => [
                            ['title' => 'Archive',                    'action' => 'archive'],
                            ['title' => 'Mark / Unmark as Printed',   'action' => 'mark-printed'],
                            ['title' => 'Mark / Unmark as Packing',   'action' => 'mark-packing'],
                        ]
                    ]
                ]
            ]
        );
        $bulkItems->setTemplate('layout/bulk-actions');
        return $bulkItems;
    }

    protected function getFilterBar()
    {
        $viewRender = $this->getServiceLocator()->get('Mustache\View\Renderer');

        $filterRows = [];
        $filterRow = [];

        $dateFormat = 'd/m/y';
        $dateRangeOptions = [
            [
                'title' => 'All Time',
                'from'  => 'All',
                'to'    => 'All'
            ],
            [
                'title' => 'Today',
                'from'  => date($dateFormat),
                'to'    => date($dateFormat)
            ],
            [
                'title' => 'Last 7 days',
                'from'  => date($dateFormat, strtotime("-7 days")),
                'to'    => date($dateFormat)
            ],
            [
                'title' => 'Month to date',
                'from'  => date($dateFormat, strtotime( 'first day of ' . date( 'F Y'))),
                'to'    => date($dateFormat)
            ],
            [
                'title' => 'Year to date',
                'from'  => date($dateFormat,  strtotime( 'first day of January ' . date('Y'))),
                'to'    => date($dateFormat)
            ],
            [
                'title' => 'The previous month',
                'from'  => date($dateFormat, strtotime( 'first day of last month ')),
                'to'    => date($dateFormat, strtotime( 'last day of last month ')),
            ]
        ];
        $dateRangeFilter = $this->getViewModelFactory()->newInstance();
        $dateRangeFilter->setTemplate('elements/date-range');
        $dateRangeFilter->setVariable('options', $dateRangeOptions);
        $filterRow[] = $viewRender->render($dateRangeFilter);

        $options =[
            'title' => "Status",
            'id'    => 'filter-status',
            'options' => [
                ['href' => '#', 'class' => '', 'title' => 'New'],
                ['href' => '#', 'class' => '', 'title' => 'Processing'],
                ['href' => '#', 'class' => '', 'title' => 'Dispatched']
            ]
        ];
        $customSelect = $this->getViewModelFactory()->newInstance();
        $customSelect->setTemplate('elements/custom-select');
        $customSelect->setVariable('options', $options);
        $options['customSelect'] = $viewRender->render($customSelect);

        $statusFilter = $this->getViewModelFactory()->newInstance();
        $statusFilter->setTemplate('elements/custom-select');
        $statusFilter->setVariable('options', $options);
        $filterRow[] = $viewRender->render($statusFilter);

        $options = [
            'title' => 'Contains Text',
            'placeholder' => 'Contains Text...',
            'class' => '',
            'value' => ''
        ];
        $statusFilter = $this->getViewModelFactory()->newInstance();
        $statusFilter->setTemplate('elements/text');
        $statusFilter->setVariable('options', $options);
        $filterRow[] = $viewRender->render($statusFilter);

        $options = ['Account','Channel','Include Country','Exclude Country','Show Archived','Multi-Line Orders','Multiple Same Item','Flags','Columns']; 
        $filter = $this->getViewModelFactory()->newInstance();
        $filter->setTemplate('elements/custom-select-group');
        $filter->setVariable('options', $options);
        $filterRow[] = $viewRender->render($filter);

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
            'options' => ['UK','Austria','Croatia','Cyprus','France','Germany','Italy','Spain'],
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

    public function listAction()
    {
        return $this->getJsonModelFactory()->newInstance(
            [
                'Records' => []
            ]
        );
    }
}