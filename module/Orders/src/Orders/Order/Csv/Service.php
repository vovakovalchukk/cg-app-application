<?php
namespace Orders\Order\Csv;

use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use League\Csv\Writer as CsvWriter;
use Orders\Order\PickList\ProgressStorage;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected $mapper;
    protected $progressStorage;

    public function __construct(Mapper $mapper, ProgressStorage $progressStorage)
    {
        $this->setMapper($mapper)
            ->setProgressStorage($progressStorage);
    }

    const MIME_TYPE = 'text/csv';
    const FILENAME = 'orders.csv';

    public function getResponseFromOrderCollection(OrderCollection $orders, $progressKey = null)
    {
        $csvWriter = CsvWriter::createFromFileObject(new \SplTempFileObject(-1));
        $mapper = $this->getMapper();
        $linesAll = $mapper->fromOrderCollection($orders);
        $csvWriter->insertOne($mapper->getOrderAndItemsHeaders());
        $csvWriter->insertAll($linesAll);
        return new Response(static::MIME_TYPE, static::FILENAME, (string) $csvWriter);
    }

    public function checkToCsvGenerationProgress($key)
    {
        return (int) $this->getProgressStorage()->getProgress($key);
    }

    public function triggerIntercomEvent()
    {
        
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
}