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

    /** @var OrdersMapper $ordersMapper */
    protected $ordersMapper;
    /** @var OrdersItemsMapper $ordersItemsMapper */
    protected $ordersItemsMapper;
    /** @var ProgressStorage $progressStorage */
    protected $progressStorage;
    /** @var ActiveUserContainer $activeUserContainer */
    protected $activeUserContainer;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;

    public function __construct(
        OrdersMapper $ordersMapper,
        OrdersItemsMapper $ordersItemsMapper,
        ProgressStorage $progressStorage,
        ActiveUserContainer $activeUserContainer,
        IntercomEventService $intercomEventService
    ) {
        $this
            ->setOrdersMapper($ordersMapper)
            ->setOrdersItemsMapper($ordersItemsMapper)
            ->setProgressStorage($progressStorage)
            ->setActiveUserContainer($activeUserContainer)
            ->setIntercomEventService($intercomEventService);
    }

    public function generateCsvForOrders(OrderCollection $orders, $progressKey = null)
    {
        $mapper = $this->ordersMapper;
        $csv = $this->generateCsv($mapper->getHeaders(), $mapper->fromOrderCollection($orders), $progressKey);
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvFromFilterForOrders(OrderFilter $filter, $progressKey = null)
    {
        $mapper = $this->ordersMapper;
        $csv = $this->generateCsv($mapper->getHeaders(), $mapper->fromOrderFilter($filter), $progressKey);
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvForOrdersAndItems(OrderCollection $orders, $progressKey = null)
    {
        $mapper = $this->ordersItemsMapper;
        $csv = $this->generateCsv($mapper->getHeaders(), $mapper->fromOrderCollection($orders), $progressKey);
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvFromFilterForOrdersAndItems(OrderFilter $filter, $progressKey = null)
    {
        $mapper = $this->ordersItemsMapper;
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
                $this->progressStorage->setProgress($progressKey, $count);
            }
        }
        $this->endProgress($progressKey);
        return $csvWriter;
    }

    public function checkToCsvGenerationProgress($progressKey)
    {
        $count = $this->progressStorage->getProgress($progressKey);
        if ($count === null) {
            return null;
        }
        return (int)$count;
    }

    public function startProgress($progressKey)
    {
        $this->progressStorage->setProgress($progressKey, 0);
    }

    protected function endProgress($progressKey)
    {
        if (!$progressKey) {
            return;
        }
        $this->progressStorage->removeProgress($progressKey);
    }

    protected function notifyOfGeneration()
    {
        $event = new IntercomEvent(static::EVENT_CSV_GENERATED, $this->activeUserContainer->getActiveUser()->getId());
        $this->intercomEventService->save($event);
    }

    /**
     * @return self
     */
    public function setOrdersItemsMapper(OrdersItemsMapper $ordersItemsMapper)
    {
        $this->ordersItemsMapper = $ordersItemsMapper;
        return $this;
    }

    /**
     * @return self
     */
    public function setOrdersMapper(OrdersMapper $ordersMapper)
    {
        $this->ordersMapper = $ordersMapper;
        return $this;
    }

    /**
     * @return self
     */
    public function setProgressStorage(ProgressStorage $progressStorage)
    {
        $this->progressStorage = $progressStorage;
        return $this;
    }

    /**
     * @return self
     */
    public function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    /**
     * @return self
     */
    public function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }
}
