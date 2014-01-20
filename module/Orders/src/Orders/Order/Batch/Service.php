<?php
namespace Orders\Order\Batch;

use CG\Order\Shared\Batch\StorageInterface as BatchInterface;
use CG\User\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Service\Filter;
use CG\Order\Shared\Batch\Entity as BatchEntity;
use CG\Order\Shared\StorageInterface as OrderInterface;

class Service
{
    protected $organisationUnitService;
    protected $batchClient;
    protected $orderClient;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;
    const ACTIVE = 1;
    const DEFAULT_INCLUDE_ARCHIVED = 1;

    public function __construct(OrganisationUnitService $organisationUnitService, BatchInterface $batchClient,
                                OrderInterface $orderClient)
    {
        $this->setOrganisationUnitService($organisationUnitService)
            ->setBatchClient($batchClient)
            ->setOrderClient($orderClient);
    }

    public function getBatches()
    {
        $organisationUnitIds = $this->getOrganisationUnitService()->getAncestorOrganisationUnitIds();
        try {
            $batchCollection = $this->getBatchClient()->fetchCollectionByPagination(static::DEFAULT_LIMIT,
                static::DEFAULT_PAGE, $organisationUnitIds, static::ACTIVE);
        } catch (NotFound $exception) {
            $batchCollection = new \SplObjectStorage();
        }
        return $batchCollection;
    }

    public function create($orderIds)
    {
        $userEntity = $this->getOrganisationUnitService()->getActiveUser();
        $batch = $this->getDi()->get(BatchEntity::class, array(
            "organisationUnitId" => $userEntity->getOrganisationUnitId()
        ));
        $batch = $this->getBatchClient()->save($batch);

        $organisationUnitIds = $this->getOrganisationUnitService()->getAncestorOrganisationUnitIds();
        $filterEntity = $this->getDi()->get(Filter::class, array(
            "limit" => "all",
            "page" => static::DEFAULT_PAGE,
            "id" => $orderIds,
            "organisationUnitId" => $organisationUnitIds,
            "includeArchived" => static::DEFAULT_INCLUDE_ARCHIVED,
        ));
        $orders = $this->getOrderClient()->fetchCollectionByFilter($filterEntity);
        $rollback = array();
        try {
            foreach ($orders as $index => $order) {
                $rollback[$index] = $order->getBatch();
                $order->setBatch($batch->getId());
                $this->getOrderClient()->save($order);
            }
        }
        catch (\Exception $e) {
            try {
                foreach ($rollback as $index => $batchId) {
                    $orders[$index]->setBatch($batchId);
                    $this->getOrderClient()->save($orders[$index]);
                }
                $this->delete($batchId->getId());
            } catch (\Exception $e) {
                //Shits Really Hit The Fan
            }
            throw $e;
        }
    }

    public function delete($batchId)
    {
        $entity = $this->getBatchClient()->fetch($batchId);
        $this->getBatchClient()->remove($entity);
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

    public function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    public function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
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
}