<?php
namespace Orders\Order;

use CG_UI\View\DataTable;
use CG\Order\Shared\StorageInterface;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter\Entity as Filter;
use CG\Order\Service\Filter\Mapper as FilterMapper;
use Zend\Session\SessionManager;

class Service
{
    protected $ordersTable;
    protected $orderClient;
    protected $activeUserContainer;
    protected $filter;
    protected $filterMapper;
    protected $sessionManager;

    public function __construct(
        DataTable $ordersTable,
        StorageInterface $orderClient,
        ActiveUserInterface $activeUserContainer,
        Filter $filter,
        FilterMapper $filterMapper,
        SessionManager $sessionManager
    )
    {
        $this
            ->setOrdersTable($ordersTable)
            ->setOrderClient($orderClient)
            ->setActiveUserContainer($activeUserContainer)
            ->setFilter($filter)
            ->setFilterMapper($filterMapper)
            ->setSessionManager($sessionManager);
    }

    public function setOrdersTable($ordersTable)
    {
        $this->ordersTable = $ordersTable;
        return $this;
    }

    public function getOrdersTable()
    {
        return $this->ordersTable;
    }

    public function setOrderClient($orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    public function getOrderClient()
    {
        return $this->orderClient;
    }

    public function setActiveUserContainer($activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    public function setSessionFilter(Filter $filter)
    {
        $session = $this->getSessionStorage();
        if (!isset($session['orders'])) {
            $session['orders'] = [];
        }
        $session['orders']['filter'] = $filter;
        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
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

    public function setFilterMapper(FilterMapper $filterMapper)
    {
        $this->filterMapper = $filterMapper;
        return $this;
    }

    public function getFilterMapper()
    {
        return $this->filterMapper;
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

    public function getSessionStorage()
    {
        return $this->getSessionManager()->getStorage();
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
}