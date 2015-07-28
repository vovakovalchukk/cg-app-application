<?php
namespace Products\Stock\Csv;

use CG\User\ActiveUserInterface;
use League\Csv\Writer as CsvWriter;
use CG\Stock\Service as StockService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Import\File\Storage\Db as ImportFileStorage;
use CG\Stock\Import\File\Mapper as ImportFileMapper;
use GearmanClient;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;

class Service
{
    const MIME_TYPE = "text/csv";
    const FILENAME = "stock.csv";
    const COLLECTION_SIZE = 200;
    const EVENT_STOCK_IMPORT = "Stock Levels Imported";
    const EVENT_STOCK_EXPORT = "Stock Levels Exported";

    protected $activeUserContainer;
    protected $stockService;
    protected $mapper;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        StockService $stockService,
        Mapper $mapper,
        ImportFileStorage $importFileStorage,
        ImportFileMapper $importFileMapper,
        GearmanClient $gearmanClient,
        IntercomEventService $intercomEventService
    ) {
        $this->setActiveUserInterface($activeUserContainer)
            ->setStockService($stockService)
            ->setMapper($mapper)
            ->setImportFileStorage($importFileStorage)
            ->setImportFileMapper($importFileMapper)
            ->setGearmanClient($gearmanClient)
            ->setIntercomEventService($intercomEventService);
    }

    public function uploadCsvForActiveUser($updateOption, array $file)
    {
        $this->notifyOfUpload();
        return $this->uploadCsvForOu(
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            $updateOption
        );
    }

    public function uploadCsvForOu(
        $organisationUnitId,
        $updateOption,
        array $file
    ) {
        $fileEntity = $this->saveFile($updateOption, $file);
        $this->createJob($fileEntity, $organisationUnitId);
    }

    protected function saveFile($updateOption, array $file)
    {
        return $this->importFileStorage->save(
            $this->importFileMapper->fromUpload($updateOption, $file)
        );
    }

    protected function createJob(ImportFile $fileEntity, $organisationUnitId)
    {
        $fileId = $fileEntity->getId();
        $this->gearmanClient->doBackground(
            "stockImportFile",
            new ImportWorkload($organisationUnitId, $fileId),
            $fileId . "-" . $organisationUnitId
        );
    }

    public function generateCsvForActiveUser()
    {
        $this->notifyOfExport();
        return $this->generateCsvForOu(
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
        );
    }

    public function generateCsvForOu($organisationUnitId)
    {
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
            "stock on hand"
        ];
    }

    protected function notifyOfExport()
    {
        return $this->notifyIntercom(static::EVENT_STOCK_EXPORT);
    }

    protected function notifyOfUpload()
    {
        return $this->notifyIntercom(static::EVENT_STOCK_IMPORT);
    }

    protected function notifyIntercom($event)
    {
        $event = new IntercomEvent($event, $this->getActiveUserId());
        $this->intercomEventService->save($event);
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
