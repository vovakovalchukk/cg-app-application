<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\DataTable;
use CG\Order\Client\Batch\Storage\Api as BatchApi;
use CG\User\Storage\Api as UserApi;
use CG\Order\Shared\StorageInterface;
use CG\Order\Service\Filter\Entity as Filter;

class OrdersController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $ordersTable;
    protected $userApi;
    protected $batchApi;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;
    const ACTIVE = 1;

    public function __construct(JsonModelFactory $jsonModelFactory, ViewModelFactory $viewModelFactory, DataTable $ordersTable,
                                StorageInterface $orderClient, UserApi $userApi, BatchApi $batchApi)
    {
        $this
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setOrdersTable($ordersTable)
            ->setUserApi($userApi)
            ->setBatchApi($batchApi)
            ->setOrderClient($orderClient);
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

    public function setBatchApi(BatchApi $batchApi)
    {
        $this->batchApi = $batchApi;
        return $this;
    }

    public function getBatchApi()
    {
        return $this->batchApi;
    }

    public function setUserApi(UserApi $userApi)
    {
        $this->userApi = $userApi;
        return $this;
    }

    public function getUserApi()
    {
        return $this->userApi;

    }
    public function setOrderClient(StorageInterface $orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    public function getOrderClient()
    {
        return $this->orderClient;
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $this->getServiceLocator()
            ->get('viewhelpermanager')
            ->get('HeadScript')->appendFile('/channelgrabber/zf2-v4-ui/js/order.js', "text/javascript");

        $ordersTable = $this->getOrdersTable();
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
                            ['title' => 'Add', 'action' => 'add-to-batch']
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
        $organisationUnitIds = array();
        $batchCollection = $this->getBatchApi()->fetchCollectionByPagination(static::DEFAULT_LIMIT, static::DEFAULT_PAGE,
            $organisationUnitIds, static::ACTIVE);
        $sidebar->setVariable('batches', $batchCollection);
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

        $filter = new Filter(
            $limit,
            $page,
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );

        $orders = $this->getOrderClient()->fetchCollectionByFilter($filter);

        $data['iTotalRecords'] = (int) $orders->getTotal();
        $data['iTotalDisplayRecords'] = (int) $orders->getTotal();

        foreach ($orders as $order) {
            $data['Records'][] = $order->toArray();
        }

        return $this->getJsonModelFactory()->newInstance($data);
    }
}