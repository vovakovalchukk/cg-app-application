<?php
namespace Orders\Order\Batch;

use CG\Order\Shared\Batch\StorageInterface as BatchClient;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\User\ActiveUserInterface;
use CG\User\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Service\Filter;
use CG\Order\Shared\Batch\Entity as BatchEntity;
use CG\Order\Client\Service as OrderClient;
use Zend\Di\Di;
use Predis\Client as PredisClient;
use CG\Stdlib\Exception\Runtime\RequiredKeyMissing;
use CG\Order\Shared\Collection as Orders;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Log\LoggerAwareInterface;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    const STAT_ORDER_ACTION_BATCHED = 'orderAction.batched.%s.%d.%d';

    protected $organisationUnitService;
    protected $batchClient;
    protected $orderClient;
    protected $di;
    protected $redisClient;
    protected $activeUserContainer;

    const LOG_CODE = "OrderBatchService";
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
        PredisClient $redisClient,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->setOrganisationUnitService($organisationUnitService)
            ->setBatchClient($batchClient)
            ->setOrderClient($orderClient)
            ->setDi($di)
            ->setRedisClient($redisClient)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function getBatches(?bool $active = true): array
    {
        $organisationUnitIds = $this->getOrganisationUnitService()->getAncestorOrganisationUnitIdsByActiveUser();
        try {
            $batchCollection = $this->getBatchClient()->fetchCollectionByPagination(static::DEFAULT_LIMIT,
                static::DEFAULT_PAGE, $organisationUnitIds, $active);
            $batches = $batchCollection->toArray();
            usort($batches, [$this, "compare"]);
        } catch (NotFound $exception) {
            $batches = [];
        }
        return $batches;
    }

    protected function compare($a, $b)
    {
        return strnatcmp($a['name'], $b['name']);
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

    /**
     * @deprecated Use createFromFilter()
     */
    public function create(Orders $orders)
    {
        if (empty($orders)) {
            throw new RequiredKeyMissing('No Orders provided');
        }

        $this->logInfo("Creating batch with orders: %s", implode(", ", $orders->getIds()));
        $batch = $this->createBatch();
        $this->updateOrders($orders, $batch->getName());
    }

    public function createFromFilter(Filter $filter)
    {
        $this->logDebug("Creating batch from filter", [], 'OrdersBatchService');
        $batch = $this->createBatch();
        $this->updateOrdersByFilter($filter, $batch->getName());
    }

    public function remove(Orders $orders)
    {
        if (empty($orders)) {
            throw new RequiredKeyMissing('No Orders provided');
        }

        $this->updateOrders($orders);
    }

    public function removeByFilter(Filter $filter)
    {
        $this->updateOrdersByFilter($filter);
    }

    public function areOrdersAssociatedWithAnyBatch($orderIds)
    {
        $filter = new Filter();
        $filter->setOrderIds($orderIds);
        $orders = $this->getOrderClient()->fetchCollectionByFilter($filter);

        $batchMap = [];
        foreach($orders as $order)
        {
            $batchMap[] = [
                'orderId' => $order->getId(),
                'batch' => ($order->getBatch() ? $order->getBatch() : 0),
            ];
        }
        $this->logPrettyDebug('The following batch map was generated:', $batchMap, [], [static::LOG_CODE, __FUNCTION__]);
        return $batchMap;
    }

    protected function createBatch()
    {
        $rootOuId = $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
        $id = $this->getRedisClient()->incr(static::BATCH_KEY . $rootOuId);
        $batch = $this->getDi()->get(BatchEntity::class, array(
            "organisationUnitId" => $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            "active" => true,
            "id" => $this->generateBatchId($rootOuId, $id),
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
                $this->statsIncrement(
                    static::STAT_ORDER_ACTION_BATCHED, [
                        $order->getChannel(),
                        $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId(),
                        $this->getActiveUserContainer()->getActiveUser()->getId()
                    ]
                );
            } catch (NotModified $exception) {
                // Batch already correct - ignore
            }
        }
    }

    protected function updateOrdersByFilter(Filter $filter, $batch = null)
    {
        // Use patching as its faster than saving the individual orders
        $this->orderClient->patchCollectionByFilterObject($filter, ['batch' => $batch]);
    }

    protected function generateBatchId($rootOu, $increment)
    {
        return $rootOu . "-" . $increment;
    }

    public function setInactive($batchId)
    {
        $batch = $this->getBatchClient()->fetch($batchId);
        $batch->setActive(false);
        $this->getBatchClient()->remove($batch);
        $this->getBatchClient()->save($batch);
    }

    public function setBatchClient(BatchClient $batchClient)
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

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }
}
