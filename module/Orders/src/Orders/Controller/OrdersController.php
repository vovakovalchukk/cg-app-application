<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;

class OrdersController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $viewModelFactory;

    public function __construct(JsonModelFactory $jsonModelFactory, ViewModelFactory $viewModelFactory)
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory);
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

    public function listAction()
    {
        return $this->getJsonModelFactory()->newInstance();
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();

        $tableColumns = [
            ['title' => 'Channel', 'mData' => 'channel'],
            ['title' => 'Account', 'mData' => 'account'],
            ['title' => 'Order Date', 'mData' => 'purchaseDate'],
            ['title' => 'Order Information', 'mData' => 'orderInformation'],
            ['title' => 'Total', 'mData' => 'total'],
            ['title' => 'Buyer', 'mData' => 'buyerName'],
            ['title' => 'Status', 'mData' => 'status'],
            ['title' => 'Batch', 'mData' => 'batch'],
            ['title' => 'Messages', 'mData' => 'messages'],
            ['title' => 'Shipping Method', 'mData' => 'shippingMethod'],
            ['title' => 'Dispatch', 'mData' => 'shippingMethod'],
            ['title' => 'Print', 'mData' => 'shippingMethod'],
            ['title' => 'COG HOW DO I PICTURE HERE', 'mData' => '?'],
        ];

        $orders = $this->getViewModelFactory()->newInstance();
        $orders->setTemplate('table/table');
        $orders->setVariable('tableJSVars', [
            'source' => $this->url()->fromRoute('Orders/ajax'),
            'columns' => $tableColumns,
            'limit' => 500
        ]);
        $orders->setVariable('datatableJSVars', [
            'tableId' => 'orders-table',
            'filterFormId' => 'filters'
        ]);

        $orders->setVariable('tableColumns', $tableColumns);
        $view->addChild($orders, 'orders');

        return $view;
    }
}