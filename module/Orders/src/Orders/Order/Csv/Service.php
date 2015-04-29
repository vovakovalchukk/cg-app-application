<?php
namespace Orders\Order\Csv;

use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use League\Csv\Writer as CsvWriter;
use Orders\Order\PickList\ProgressStorage;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const EVENT_CSV_GENERATED = 'Orders CSV Generated';

    const MIME_TYPE = 'text/csv';
    const FILENAME = 'orders.csv';

    protected $mapper;
    protected $progressStorage;
    protected $activeUserContainer;
    protected $intercomEventService;

    public function __construct(
        Mapper $mapper,
        ProgressStorage $progressStorage,
        ActiveUserContainer $activeUserContainer,
        IntercomEventService $intercomEventService
    ) {
        $this->setMapper($mapper)
            ->setProgressStorage($progressStorage)
            ->setActiveUserContainer($activeUserContainer)
            ->setIntercomEventService($intercomEventService);
    }

    public function getResponseFromOrderCollection(OrderCollection $orders, $progressKey = null)
    {
        $orders->rewind();
        $csvWriter = CsvWriter::createFromFileObject(new \SplTempFileObject(-1));
        $csvWriter->insertOne($this->getMapper()->getOrderAndItemsHeaders());
        $rowsGenerator = $this->getMapper()->fromOrderCollection($orders);
        foreach($rowsGenerator as $rows) {
            $csvWriter->insertAll($rows);
        }
        $this->notifyOfGeneration();
        return new Response(static::MIME_TYPE, static::FILENAME, (string) $csvWriter);
    }

    public function checkToCsvGenerationProgress($key)
    {
        return (int) $this->getProgressStorage()->getProgress($key);
    }

    protected function notifyOfGeneration()
    {
        $event = new IntercomEvent(static::EVENT_CSV_GENERATED, $this->getActiveUserContainer()->getActiveUser()->getId());
        $this->getIntercomEventService()->save($event);
    }

    /**
     * @return Mapper
     */
    protected function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param Mapper $mapper
     * @return $this
     */
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
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