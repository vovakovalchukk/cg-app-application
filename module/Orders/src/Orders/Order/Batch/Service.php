<?php
namespace Orders\Order\Batch;

use CG\Order\Shared\Batch\StorageInterface as BatchClient;
use CG\User\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Service\Filter;
use CG\Order\Shared\Batch\Entity as BatchEntity;
use CG\Order\Client\StorageInterface as OrderClient;
use Zend\Di\Di;
use Guzzle\Common\Exception\GuzzleException;
use Predis\Client as PredisClient;
use CG\Stdlib\Exception\Runtime\RequiredKeyMissing;
use CG\Order\Shared\Collection as Orders;
use CG\Http\Exception\Exception3xx\NotModified;

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

    public function __construct(
        OrganisationUnitService $organisationUnitService,
        BatchClient $batchClient,
        OrderClient $orderClient,
        Di $di,
        PredisClient $redisClient
    ) {
        $this->setOrganisationUnitService($organisationUnitService)
            ->setBatchClient($batchClient)
            ->setOrderClient($orderClient)
            ->setDi($di)
            ->setRedisClient($redisClient);
    }

    public function getBatches()
    {
        $organisationUnitIds = $this->getOrganisationUnitService()->getAncestorOrganisationUnitIdsByActiveUser();
        try {
            $batchCollection = $this->getBatchClient()->fetchCollectionByPagination(static::DEFAULT_LIMIT,
                static::DEFAULT_PAGE, $organisationUnitIds, static::ACTIVE);
            $batches = $batchCollection->toArray();
        } catch (NotFound $exception) {
            $batches = array();
        }
        return $batches;
    }

    /**
     * @param array $orderIds
     * @return Filter
     */
    protected function getOrderFilter(array $orderIds)
    {
        return $this->getDi()->newInstance(
            Filter::class,
            [
                'page' => 1,
                'limit' => 'all',
                'orderIds' => $orderIds,
            ]
        );
    }

    public function createForOrders(array $orderIds)
    {
        $this->create(
            $this->getOrderClient()->fetchCollectionByFilter(
                $this->getOrderFilter($orderIds)
            )
        );
    }

    public function createForFilterId($filterId)
    {
        $this->create(
            $this->getOrderClient()->fetchCollectionByFilterId(
                $filterId,
                'all',
                1,
                null,
                null
            )
        );
    }

    protected function create(Orders $orders)
    {
        if (empty($orders)) {
            throw new RequiredKeyMissing('No Orders provided');
        }

        $batch = $this->createBatch();
        $this->updateOrders($orders, $batch->getName());
    }

    public function unsetForOrders(array $orderIds)
    {
        $this->unsetBatch(
            $this->getOrderClient()->fetchCollectionByFilter(
                $this->getOrderFilter($orderIds)
            )
        );
    }

    public function unsetForFilterId($filterId)
    {
        $this->unsetBatch(
            $this->getOrderClient()->fetchCollectionByFilterId(
                $filterId,
                'all',
                1,
                null,
                null
            )
        );
    }

    protected function unsetBatch(Orders $orders)
    {
        if (empty($orders)) {
            throw new RequiredKeyMissing('No Orders provided');
        }

        $this->updateOrders($orders);
    }

    protected function createBatch()
    {
        $userEntity = $this->getOrganisationUnitService()->getActiveUser();
        $rootOu = $this->getOrganisationUnitService()->getRootOuByActiveUser();
        $id = $this->getRedisClient()->incr(static::BATCH_KEY . $rootOu->getId());
        $batch = $this->getDi()->get(BatchEntity::class, array(
            "organisationUnitId" => $userEntity->getOrganisationUnitId(),
            "active" => true,
            "id" => $this->generateBatchId($rootOu->getId(), $id),
            "name" => (string) $id
        ));
        $batch = $this->getBatchClient()->save($batch);
        return $batch;
    }

    protected function updateOrders(Orders $orders, $batch = null)
    {
        foreach ($orders as $order) {
            try {
                $this->getOrderClient()->save(
                    $order->setBatch($batch)
                );
            } catch (NotModified $exception) {
                // Batch already correct - ignore
            }
        }
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

    public function setBatchClient(BatchClient $batchClient)
    {
        $this->batchClient = $batchClient;
        return $this;
    }

    /**
     * @return BatchClient
     */
    public function getBatchClient()
    {
        return $this->batchClient;
    }

    public function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    /**
     * @return OrganisationUnitService
     */
    public function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    public function setOrderClient(OrderClient $orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    /**
     * @return OrderClient
     */
    public function getOrderClient()
    {
        return $this->orderClient;
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }

    public function setRedisClient(PredisClient $redisClient)
    {
        $this->redisClient = $redisClient;
        return $this;
    }

    /**
     * @return PredisClient
     */
    public function getRedisClient()
    {
        return $this->redisClient;
    }
}