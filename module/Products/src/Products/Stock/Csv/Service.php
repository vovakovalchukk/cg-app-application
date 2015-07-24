<?php
namespace Products\Stock\Csv;

use CG\User\ActiveUserInterface;
use League\Csv\Writer as CsvWriter;
use CG\Stock\Service as StockService;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    const MIME_TYPE = "text/csv";
    const FILENAME = "stock.csv";
    const COLLECTION_SIZE = 200;

    protected $activeUserContainer;
    protected $stockService;
    protected $mapper;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        StockService $stockService,
        Mapper $mapper
    ) {
        $this->setActiveUserInterface($activeUserContainer)
            ->setStockService($stockService)
            ->setMapper($mapper);
    }

    public function generateCsvForActiveUser()
    {
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
}
