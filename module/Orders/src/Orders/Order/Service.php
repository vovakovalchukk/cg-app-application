<?php
namespace Orders\Order;

use CG_UI\View\DataTable;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter\Entity as Filter;
use CG\Order\Shared\StorageInterface as OrderInterface;
use CG\Order\Shared\Batch\StorageInterface as BatchInterface;
use CG\OrganisationUnit\StorageInterface as OrganisationUnitInterface;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    protected $ordersTable;
    protected $activeUserContainer;
    protected $orderClient;
    protected $organisationUnitClient;
    protected $batchClient;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;
    const ACTIVE = 1;

    public function __construct(DataTable $ordersTable, ActiveUserInterface $activeUserContainer,
                                OrderInterface $orderClient, OrganisationUnitInterface $organisationUnitClient,
                                BatchInterface $batchClient)
    {
        $this->setOrdersTable($ordersTable)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrderClient($orderClient)
            ->setOrganisationUnitClient($organisationUnitClient)
            ->setBatchClient($batchClient);
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

    public function getBatches()
    {
        $userEntity = $this->getActiveUser();
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
        return $batchCollection;
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

    public function setBatchClient(BatchInterface $batchClient)
    {
        $this->batchClient = $batchClient;
        return $this;
    }

    public function getBatchClient()
    {
        return $this->batchClient;
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

    public function setOrganisationUnitClient(OrganisationUnitInterface $organisationUnitClient)
    {
        $this->organisationUnitClient = $organisationUnitClient;
        return $this;
    }

    public function getOrganisationUnitClient()
    {
        return $this->organisationUnitClient;
    }
}