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

    public function __construct(
        DataTable $ordersTable,
        StorageInterface $orderClient,
        ActiveUserInterface $activeUserContainer
    )
    {
        $this
            ->setOrdersTable($ordersTable)
            ->setOrderClient($orderClient)
            ->setActiveUserContainer($activeUserContainer);
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

    public function getOrders($limit, $page, array $filters = [])
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
}