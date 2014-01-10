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
        $bulkItems->setTemplate('orders/orders/bulk-actions');
        $view->addChild($bulkItems, 'bulkItems');

        return $view;
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