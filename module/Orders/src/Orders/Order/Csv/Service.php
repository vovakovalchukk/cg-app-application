<?php
namespace Orders\Order\Csv;

use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use Generator;
use League\Csv\Writer as CsvWriter;
use Orders\Order\Csv\Mapper\Orders as OrdersMapper;
use Orders\Order\Csv\Mapper\OrdersItems as OrdersItemsMapper;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const EVENT_CSV_GENERATED = 'Orders CSV Generated';

    const MIME_TYPE = 'text/csv';
    const FILENAME = 'orders.csv';

    protected $ordersMapper;
    protected $ordersItemsMapper;
    protected $progressStorage;
    protected $activeUserContainer;
    protected $intercomEventService;

    public function __construct(
        OrdersMapper $ordersMapper,
        OrdersItemsMapper $ordersItemsMapper,
        ProgressStorage $progressStorage,
        ActiveUserContainer $activeUserContainer,
        IntercomEventService $intercomEventService
    ) {
        $this->setOrdersMapper($ordersMapper)
            ->setOrdersItemsMapper($ordersItemsMapper)
            ->setProgressStorage($progressStorage)
            ->setActiveUserContainer($activeUserContainer)
            ->setIntercomEventService($intercomEventService);
    }

    public function generateCsvForOrders(OrderCollection $orders, $progressKey = null)
    {
        $mapper = $this->getOrdersMapper();
        $csv = $this->generateCsv($mapper->getHeaders(), $mapper->fromOrderCollection($orders), $progressKey);
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvFromFilterForOrders(OrderFilter $filter, $progressKey = null)
    {
        $mapper = $this->getOrdersMapper();
        $csv = $this->generateCsv($mapper->getHeaders(), $mapper->fromOrderFilter($filter), $progressKey);
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvForOrdersAndItems(OrderCollection $orders, $progressKey = null)
    {
        $mapper = $this->getOrdersItemsMapper();
        $csv = $this->generateCsv($mapper->getHeaders(), $mapper->fromOrderCollection($orders), $progressKey);
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvFromFilterForOrdersAndItems(OrderFilter $filter, $progressKey = null)
    {
        $mapper = $this->getOrdersItemsMapper();
        $csv = $this->generateCsv($mapper->getHeaders(), $mapper->fromOrderFilter($filter), $progressKey);
        $this->notifyOfGeneration();
        return $csv;
    }

    protected function generateCsv($headers, Generator $rowsGenerator, $progressKey = null)
    {
        $csvWriter = CsvWriter::createFromFileObject(new \SplTempFileObject(-1));
        $csvWriter->insertOne($headers);
        $count = 0;
        foreach($rowsGenerator as $rows) {
            $csvWriter->insertAll($rows);
            $count += count($rows);
            if ($progressKey) {
                $this->getProgressStorage()->setProgress($progressKey, $count);
            }
        }
        $this->endProgress($progressKey);
        return $csvWriter;
    }

    public function checkToCsvGenerationProgress($progressKey)
    {
        $count = $this->getProgressStorage()->getProgress($progressKey);
        if ($count === null) {
            return null;
        }
        return (int)$count;
    }

    public function startProgress($progressKey)
    {
        $this->getProgressStorage()->setProgress($progressKey, 0);
    }

    protected function endProgress($progressKey)
    {
        if (!$progressKey) {
            return;
        }
        $this->getProgressStorage()->removeProgress($progressKey);
    }

    protected function notifyOfGeneration()
    {
        $event = new IntercomEvent(static::EVENT_CSV_GENERATED, $this->getActiveUserContainer()->getActiveUser()->getId());
        $this->getIntercomEventService()->save($event);
    }

    /**
     * @return OrdersItemsMapper
     */
    protected function getOrdersItemsMapper()
    {
        return $this->ordersItemsMapper;
    }

    /**
     * @param OrdersItemsMapper $ordersItemsMapper
     * @return $this
     */
    public function setOrdersItemsMapper(OrdersItemsMapper $ordersItemsMapper)
    {
        $this->ordersItemsMapper = $ordersItemsMapper;
        return $this;
    }

    /**
     * @return OrdersMapper
     */
    protected function getOrdersMapper()
    {
        return $this->ordersMapper;
    }

    /**
     * @param OrdersMapper $ordersMapper
     * @return $this
     */
    public function setOrdersMapper(OrdersMapper $ordersMapper)
    {
        $this->ordersMapper = $ordersMapper;
        return $this;
    }

    /**
     * @return ProgressStorage
     */
    protected function getProgressStorage()
    {
        return $this->progressStorage;
    }

    /**
     * @param ProgressStorage $progressStorage
     * @return $this
     */
    public function setProgressStorage(ProgressStorage $progressStorage)
    {
        $this->progressStorage = $progressStorage;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getIntercomEventService()
    {
        return $this->intercomEventService;
    }

    /**
     * @param IntercomEventService $intercomEventService
     * @return $this
     */
    public function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    /**
     * @return ActiveUserContainer
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @param ActiveUserContainer $activeUserContainer
     * @return $this
     */
    public function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }
}
