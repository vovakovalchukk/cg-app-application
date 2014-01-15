<?php
namespace Orders\Order;

use CG_UI\View\DataTable;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter\Entity as Filter;
use CG\Order\Shared\StorageInterface as OrderInterface;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    protected $ordersTable;
    protected $activeUserContainer;
    protected $orderClient;
    protected $organisationUnitClient;

    public function __construct(DataTable $ordersTable, ActiveUserInterface $activeUserContainer,
                                OrderInterface $orderClient)
    {
        $this->setOrdersTable($ordersTable)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrderClient($orderClient);
    }

    public function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    public function getOrders($limit, $page)
    {
        $filter = new Filter(
            $limit,
            $page,
            [],
            [$this->getActiveUser()->getOrganisationUnitId()],
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

    public function setOrderClient(OrderInterface $orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    public function getOrderClient()
    {
        return $this->orderClient;
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
}