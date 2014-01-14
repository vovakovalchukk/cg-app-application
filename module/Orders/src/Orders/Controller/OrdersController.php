<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\DataTable;
use CG\Order\Shared\Batch\StorageInterface as BatchInterface;
use CG\OrganisationUnit\StorageInterface as OrganisationUnitInterface;
use CG\Order\Shared\StorageInterface as OrderInterface;
use Orders\Order\Service;
use CG\Stdlib\Exception\Runtime\NotFound;

class OrdersController extends AbstractActionController
{
    protected $service;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $orderClient;
    protected $organisationUnitClient;
    protected $batchClient;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;
    const ACTIVE = 1;

    public function __construct(Service $service, JsonModelFactory $jsonModelFactory, ViewModelFactory $viewModelFactory,
                                OrderInterface $orderClient, OrganisationUnitInterface $organisationUnitClient,
                                BatchInterface $batchClient)
    {
        $this
            ->setService($service)
            ->setJsonModelFactory($jsonModelFactory)
            ->setOrganisationUnitClient($organisationUnitClient)
            ->setBatchClient($batchClient)
            ->setOrderClient($orderClient)
            ->setViewModelFactory($viewModelFactory);
    }

    public function setBatchClient(BatchInterface $batchClient)
    {
        $this->batchClient = $batchClient;
        return $this;
    }

    public function getBatchClient()
    {
        return $this->batchClient;
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

    public function setOrderClient(OrderInterface $orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    public function getOrderClient()
    {
        return $this->orderClient;
    }
    
    public function setOrganisationUnitClient(OrganisationUnitInterface $organisationUnitClient)
    {
        $this->organisationUnitClient = $organisationUnitClient;
        return $this;
    }

    public function getOrganisationUnitClient()
    {
        return $this->organisationUnitClient;
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
        $this->getServiceLocator()
            ->get('viewhelpermanager')
            ->get('HeadScript')->appendFile('/channelgrabber/zf2-v4-ui/js/order.js', "text/javascript");

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

        $userEntity = $this->getService()->getActiveUser();
        try {
            $organisationUnits = $this->getOrganisationUnitClient()->fetchFiltered(static::DEFAULT_LIMIT,
                static::DEFAULT_PAGE, $userEntity->getOrganisationUnitId());
        } catch (NotFound $exception) {
            $organisationUnits = new \SplObjectStorage();
        }
        $organisationUnitIds = array($userEntity->getOrganisationUnitId());
        foreach ($organisationUnits as $organisationUnit) {
            $organisationUnitIds[] = $organisationUnit->getId();
        }
        try {
            $batchCollection = $this->getBatchClient()->fetchCollectionByPagination(static::DEFAULT_LIMIT,
                static::DEFAULT_PAGE, $organisationUnitIds, static::ACTIVE);
        } catch (NotFound $exception) {
            $batchCollection = new \SplObjectStorage();
        }
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