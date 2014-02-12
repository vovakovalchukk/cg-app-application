<?php
namespace Orders\Order\Batch;

use CG\Order\Shared\Batch\StorageInterface as BatchInterface;
use CG\User\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Service\Filter;
use CG\Order\Shared\Batch\Entity as BatchEntity;
use CG\Order\Shared\StorageInterface as OrderInterface;
use Zend\Di\Di;
use Guzzle\Common\Exception\GuzzleException;
use Predis\Client as PredisClient;
use CG\Stdlib\Exception\Runtime\RequiredKeyMissing;

class Service
{
    protected $organisationUnitService;
    protected $batchClient;
    protected $orderClient;
    protected $di;
    protected $redisClient;

    const DEFAULT_LIMIT = "all";
    const DEFAULT_PAGE = 1;
    const ACTIVE = true;
    const DEFAULT_INCLUDE_ARCHIVED = 1;
    const BATCH_KEY = "BatchIncrement-";

    public function __construct(OrganisationUnitService $organisationUnitService,
                                BatchInterface $batchClient,
                                OrderInterface $orderClient,
                                Di $di,
                                PredisClient $redisClient)
    {
        $this->setOrganisationUnitService($organisationUnitService)
            ->setBatchClient($batchClient)
            ->setOrderClient($orderClient)
            ->setDi($di)
            ->setRedisClient($redisClient);
    }

    public function getBatches()
    {
        $organisationUnitIds = $this->getOrganisationUnitService()->getAncestorOrganisationUnitIds();
        try {
            $batchCollection = $this->getBatchClient()->fetchCollectionByPagination(static::DEFAULT_LIMIT,
                static::DEFAULT_PAGE, $organisationUnitIds, static::ACTIVE);
            $batches = $batchCollection->toArray();
        } catch (NotFound $exception) {
            $batches = array();
        }
        return $batches;
    }

    public function create($orderIds)
    {
        if (!is_array($orderIds) || empty($orderIds)) {
            throw new RequiredKeyMissing('No Orders provided');
        }
        $batch = $this->createBatch();
        $this->updateOrders($orderIds, $batch->getName());
    }

    public function unsetBatch($orderIds)
    {
        if (!is_array($orderIds) || empty($orderIds)) {
            throw new RequiredKeyMissing('No Orders provided');
        }

        $this->updateOrders($orderIds);
    }

    protected function createBatch()
    {
        $userEntity = $this->getOrganisationUnitService()->getActiveUser();
        $rootOu = $this->getOrganisationUnitService()->getRootOu();
        $id = $this->getRedisClient()->incr(static::BATCH_KEY . $rootOu);
        $batch = $this->getDi()->get(BatchEntity::class, array(
            "organisationUnitId" => $userEntity->getOrganisationUnitId(),
            "active" => true,
            "id" => $this->generateBatchId($rootOu, $id),
            "name" => (string) $id
        ));
        $batch = $this->getBatchClient()->save($batch);
        return $batch;
    }

    protected function updateOrders(array $orderIds, $batch = null)
    {
        $organisationUnitIds = $this->getOrganisationUnitService()->getAncestorOrganisationUnitIds();
        $filterEntity = $this->getDi()->get(Filter::class, array(
            "limit" => "all",
            "page" => static::DEFAULT_PAGE,
            "id" => $orderIds,
            "organisationUnitIds" => $organisationUnitIds,
            "includeArchived" => static::DEFAULT_INCLUDE_ARCHIVED,
        ));
        $orders = $this->getOrderClient()->fetchCollectionByFilter($filterEntity);
        foreach ($orders as $order) {
            $order->setBatch($batch);
        }
        $this->getOrderClient()->saveCollection($orders);
    }

    protected function generateBatchId($rootOu, $increment)
    {
        return $rootOu . "-" . $increment;
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

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
    }

    public function setRedisClient(PredisClient $redisClient)
    {
        $this->redisClient = $redisClient;
        return $this;
    }

    public function getRedisClient()
    {
        return $this->redisClient;
    }
}