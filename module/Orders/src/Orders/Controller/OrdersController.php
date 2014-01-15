<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\DataTable;
use Orders\Order\Service;
use CG\Stdlib\Exception\Runtime\NotFound;

class OrdersController extends AbstractActionController
{
    protected $service;
    protected $jsonModelFactory;
    protected $viewModelFactory;

    public function __construct(Service $service, JsonModelFactory $jsonModelFactory, ViewModelFactory $viewModelFactory)
    {
        $this->setService($service)
            ->setJsonModelFactory($jsonModelFactory)
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

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();

        $ordersTable = $this->getService()->getOrdersTable();
        $settings = $ordersTable->getVariable('settings');
        $settings->setSource($this->url()->fromRoute('Orders/ajax'));
        $view->addChild($ordersTable, 'ordersTable');

        /**
         * When implementing bulk actions, need to delegate out rather than doing work in action
         */
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
                            ['title' => 'Remove', 'action' => 'remove-from-batch'],
                            ['title' => 'Add', 'action' => 'add-to-batch', 'href' => 'javascript:addBatch();']
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

        $sidebar->setVariable('batches', $this->getService()->getBatches());
        $view->addChild($sidebar, 'sidebar');

        return $view;
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

        try {
            $orders = $this->getService()->getOrders($limit, $page);

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