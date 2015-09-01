<?php
namespace Products\Stock\Csv;

use CG\User\ActiveUserInterface;
use League\Csv\Writer as CsvWriter;
use CG\Stock\Service as StockService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Import\File\Storage\Db as ImportFileStorage;
use CG\Stock\Import\File\Mapper as ImportFileMapper;
use CG\Stock\Import\File\Entity as ImportFile;
use GearmanClient;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Stock\Gearman\Workload\StockImport as ImportWorkload;

class Service
{
    const MIME_TYPE = "text/csv";
    const FILENAME = "stock.csv";
    const COLLECTION_SIZE = 200;
    const EVENT_STOCK_IMPORT = "Stock Levels Imported";
    const EVENT_STOCK_EXPORT = "Stock Levels Exported";

    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var StockService $stockService */
    protected $stockService;
    /** @var Mapper $mapper */
    protected $mapper;
    /** @var ImportFileStorage $importFileStorage */
    protected $importFileStorage;
    /** @var ImportFileMapper $importFileMapper */
    protected $importFileMapper;
    /** @var GearmanClient $gearmanClient */
    protected $gearmanClient;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        StockService $stockService,
        Mapper $mapper,
        ImportFileStorage $importFileStorage,
        ImportFileMapper $importFileMapper,
        GearmanClient $gearmanClient,
        IntercomEventService $intercomEventService
    ) {
        $this
            ->setActiveUserInterface($activeUserContainer)
            ->setStockService($stockService)
            ->setMapper($mapper)
            ->setImportFileStorage($importFileStorage)
            ->setImportFileMapper($importFileMapper)
            ->setGearmanClient($gearmanClient)
            ->setIntercomEventService($intercomEventService);
    }

    public function uploadCsvForActiveUser($updateOption, $fileContents)
    {
        return $this->uploadCsv(
            $this->getActiveUserId(),
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            $updateOption,
            $fileContents
        );
    }

    public function uploadCsv(
        $userId,
        $organisationUnitId,
        $updateOption,
        $fileContents
    ) {
        $this->notifyOfUpload($userId);
        $fileEntity = $this->saveFile($updateOption, $fileContents);
        $this->createJob($fileEntity, $organisationUnitId, $userId);
    }

    protected function saveFile($updateOption, $fileContents)
    {
        return $this->importFileStorage->save(
            $this->importFileMapper->fromUpload($updateOption, $fileContents)
        );
    }

    protected function createJob(ImportFile $fileEntity, $organisationUnitId, $userId)
    {
        $fileId = $fileEntity->getId();
        $this->gearmanClient->doBackground(
            "stockImportFile",
            serialize(new ImportWorkload($userId, $organisationUnitId, $fileId)),
            $fileId . "-" . $organisationUnitId
        );
    }

    public function generateCsvForActiveUser()
    {
        return $this->generateCsv(
            $this->getActiveUserId(),
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
        );
    }

    public function generateCsv($userId, $organisationUnitId)
    {
        $this->notifyOfExport($userId);

        $csv = CsvWriter::createFromFileObject(new \SplTempFileObject(-1));
        $csv->insertOne($this->getHeaders());

        $this->applyStockValuesToCsv($csv, $organisationUnitId);

        return $csv;
    }

    protected function applyStockValuesToCsv(CsvWriter $csv, $organisationUnitId)
    {
        try {
            $page = 1;
            while (true) {
                $stock = $this->stockService->fetchCollectionByPaginationAndFilters(
                    static::COLLECTION_SIZE,
                    $page++,
                    [],
                    [$organisationUnitId],
                    [],
                    []
                );
                $csv->insertAll($this->mapper->stockCollectionToCsvArray($stock));
            }
        } catch (NotFound $e) {
            // Do nothing, end of pagination
        }
    }

    protected function getHeaders()
    {
        return [
            "SKU",
            "Stock on Hand"
        ];
    }

    protected function notifyOfExport($userId)
    {
        return $this->notifyIntercom(static::EVENT_STOCK_EXPORT, $userId);
    }

    protected function notifyOfUpload($userId)
    {
        return $this->notifyIntercom(static::EVENT_STOCK_IMPORT, $userId);
    }

    protected function notifyIntercom($event, $userId)
    {
        $this->intercomEventService->save(
            new IntercomEvent($event, $userId)
        );
    }

    protected function getActiveUserId()
    {
        return $this->activeUserContainer->getActiveUser()->getId();
    }

    /**
     * @return self
     */
    public function setActiveUserInterface(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return self
     */
    public function setStockService(StockService $stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }

    /**
     * @return self
     */
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return self
     */
    public function setImportFileStorage(ImportFileStorage $importFileStorage)
    {
        $this->importFileStorage = $importFileStorage;
        return $this;
    }

    /**
     * @return self
     */
    public function setImportFileMapper(ImportFileMapper $importFileMapper)
    {
        $this->importFileMapper = $importFileMapper;
        return $this;
    }

    /**
     * @return self
     */
    public function setGearmanClient(GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
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
}