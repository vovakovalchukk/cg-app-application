<?php
namespace Orders\Order;

use CG_UI\View\DataTable;
use CG\Order\Shared\StorageInterface;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter\Entity as Filter;
use CG\Order\Service\Filter\Mapper as FilterMapper;

class Service
{
    protected $ordersTable;
    protected $orderClient;
    protected $activeUserContainer;
    protected $filter;
    protected $filterMapper;

    public function __construct(
        DataTable $ordersTable,
        StorageInterface $orderClient,
        ActiveUserInterface $activeUserContainer,
        Filter $filter,
        FilterMapper $filterMapper
    )
    {
        $this
            ->setOrdersTable($ordersTable)
            ->setOrderClient($orderClient)
            ->setActiveUserContainer($activeUserContainer)
            ->setFilter($filter)
            ->setFilterMapper($filterMapper);
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

    public function setFilterMapper(FilterMapper $filterMapper)
    {
        $this->filterMapper = $filterMapper;
        return $this;
    }

    public function getFilterMapper()
    {
        return $this->filterMapper;
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

        return $this->getOrderClient()->fetchCollectionByFilter($filter);
    }
}