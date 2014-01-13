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
                        'title' => 'Invoice',
                        'class' => 'invoice',
                        'sub-actions' => [
                            ['title' => 'by SKU',  'action' => 'invoices-sku'],
                            ['title' => 'by Title','action' => 'invoices-title']
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
        $bulkItems->setTemplate('orders/orders/bulk-actions');
        $view->addChild($bulkItems, 'bulkItems');

        $filters = $this->getViewModelFactory()->newInstance();
        $filters->setTemplate('orders/orders/filters');
        $view->addChild($filters, 'filters');

        $sidebar = $this->getViewModelFactory()->newInstance();
        $sidebar->setTemplate('orders/orders/sidebar');
        $view->addChild($sidebar, 'sidebar');

        return $view;
    }

    public function jsonAction()
    {
        return $this->getJsonModelFactory()->newInstance(
            [
                'Records' => []
            ]
        );
    }
}