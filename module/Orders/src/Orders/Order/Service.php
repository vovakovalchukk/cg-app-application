<?php
namespace Orders\Order;

use CG_UI\View\DataTable;
use CG\Order\Shared\StorageInterface;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter\Entity as Filter;

class Service
{
    protected $ordersTable;
    protected $orderClient;
    protected $activeUserContainer;
    protected $filter;

    public function __construct(
        DataTable $ordersTable,
        StorageInterface $orderClient,
        ActiveUserInterface $activeUserContainer,
        Filter $filter
    )
    {
        $this
            ->setOrdersTable($ordersTable)
            ->setOrderClient($orderClient)
            ->setActiveUserContainer($activeUserContainer)
            ->setFilter($filter);
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

    public function getFilter()
    {
        return $this->filter;
    }

    public function getOrders($limit, $page, array $filters = [])
    {
        $filter = $this->getFilter()
            ->setLimit($limit)
            ->setPage($page)
            ->setOrganisationUnitId([$this->getActiveUser()->getOrganisationUnitId()]);

        return $this->getOrderClient()->fetchCollectionByFilter($filter);
    }
}