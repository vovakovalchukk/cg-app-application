<?php
namespace Orders\Order;

use CG_UI\View\DataTable;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter\Entity as Filter;
use CG\Order\Shared\StorageInterface as OrderInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Service\Filter\Mapper as FilterMapper;
use Zend\Session\SessionManager;

class Service
{
    protected $ordersTable;
    protected $activeUserContainer;
    protected $orderClient;
    protected $filter;
    protected $filterMapper;
    protected $sessionManager;

    public function __construct(DataTable $ordersTable, ActiveUserInterface $activeUserContainer,
                                OrderInterface $orderClient, Filter $filter, FilterMapper $filterMapper,
                                SessionManager $sessionManager)
    {
        $this->setOrdersTable($ordersTable)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrderClient($orderClient)
            ->setFilter($filter)
            ->setFilterMapper($filterMapper)
            ->setSessionManager($sessionManager);
    }

    public function getSessionFilter()
    {
        $session = $this->getSessionStorage();
        if (!isset($session['orders'])) {
            $session['orders'] = [];
        }
        if (!isset($session['orders']['filter']) || !($session['orders']['filter'] instanceof Filter)) {
            $session['orders']['filter'] = $this->getFilter();
        }

        return $session['orders']['filter'];
    }

    public function getOrders($limit, $page, array $filters = [])
    {
        $filter = $this->getFilter()
            ->setLimit($limit)
            ->setPage($page)
            ->setOrganisationUnitId([$this->getActiveUser()->getOrganisationUnitId()]);

        if (!empty($filters)) {
            $filter->merge(
                $this->getFilterMapper()->fromArray($filters)
            );
        }

        $this->setSessionFilter($filter);
        return $this->getOrderClient()->fetchCollectionByFilter($filter);
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilterMapper(FilterMapper $filterMapper)
    {
        $this->filterMapper = $filterMapper;
        return $this;
    }

    public function getFilterMapper()
    {
        return $this->filterMapper;
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

    public function setOrdersTable(Datatable $ordersTable)
    {
        $this->ordersTable = $ordersTable;
        return $this;
    }

    public function getOrdersTable()
    {
        return $this->ordersTable;
    }

    public function setSessionManager(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
        return $this;
    }

    public function getSessionManager()
    {
        return $this->sessionManager;
    }
}