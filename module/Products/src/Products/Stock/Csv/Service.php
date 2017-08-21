<?php
namespace Products\Stock\Csv;

use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Product\Client\Service as ProductService;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Collection as Stock;
use CG\Stock\Gearman\Generator\StockImport as StockImportGearmanJobGenerator;
use CG\Stock\Import\File\Entity as ImportFile;
use CG\Stock\Import\File\Mapper as ImportFileMapper;
use CG\Stock\Import\File\Storage\StorageInterface as ImportFileStorage;
use CG\Stock\Service as StockService;
use CG\User\ActiveUserInterface;
use League\Csv\Writer as CsvWriter;

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
    /** @var ProductService $productService */
    protected $productService;
    /** @var Mapper $mapper */
    protected $mapper;
    /** @var ImportFileStorage $importFileStorage */
    protected $importFileStorage;
    /** @var ImportFileMapper $importFileMapper */
    protected $importFileMapper;
    /** @var StockImportGearmanJobGenerator $stockImportGearmanJobGenerator */
    protected $stockImportGearmanJobGenerator;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var ProgressStorage */
    protected $progressStorage;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        StockService $stockService,
        ProductService $productService,
        Mapper $mapper,
        ImportFileStorage $importFileStorage,
        ImportFileMapper $importFileMapper,
        StockImportGearmanJobGenerator $stockImportGearmanJobGenerator,
        IntercomEventService $intercomEventService,
        ProgressStorage $progressStorage
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->stockService = $stockService;
        $this->productService = $productService;
        $this->mapper = $mapper;
        $this->importFileStorage = $importFileStorage;
        $this->importFileMapper = $importFileMapper;
        $this->stockImportGearmanJobGenerator = $stockImportGearmanJobGenerator;
        $this->intercomEventService = $intercomEventService;
        $this->progressStorage = $progressStorage;
    }

    public function uploadCsvForActiveUser($updateOption, $fileContents)
    {
        $this->uploadCsv(
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
        ($this->stockImportGearmanJobGenerator)->generateJob(
            $this->saveFile($updateOption, $fileContents, $userId, $organisationUnitId)
        );
    }

    protected function saveFile($updateOption, $fileContents, $userId, $organisationUnitId): ImportFile
    {
        return $this->importFileStorage->save(
            $this->importFileMapper->fromUpload($updateOption, $fileContents, $userId, $organisationUnitId)
        );
    }

    public function generateCsvForActiveUser($progressKey = null)
    {
        return $this->generateCsv(
            $this->getActiveUserId(),
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            $progressKey
        );
    }

    public function generateCsv($userId, $organisationUnitId, $progressKey = null)
    {
        $this->notifyOfExport($userId);

        $csv = CsvWriter::createFromFileObject(new \SplTempFileObject(-1));
        $csv->insertOne($this->getHeaders());

        $this->applyStockValuesToCsv($csv, $organisationUnitId, $progressKey);

        return $csv;
    }

    protected function applyStockValuesToCsv(CsvWriter $csv, $organisationUnitId, $progressKey = null)
    {
        try {
            $page = 1;
            while (true) {
                /** @var Stock $stock */
                $stock = $this->stockService->fetchCollectionByPaginationAndFilters(
                    static::COLLECTION_SIZE,
                    $page++,
                    [],
                    [$organisationUnitId],
                    [],
                    []
                );

                try {
                    $products = $this->productService->fetchCollectionByFilter(
                        (new ProductFilter('all'))
                            ->setReplaceVariationWithParent(true)
                            ->setOrganisationUnitId([$organisationUnitId])
                            ->setSku(array_values($stock->getArrayOf('sku')))
                    );

                    /** @var Product $product */
                    foreach ((clone $products) as $product) {
                        if (!$product->isParent()) {
                            continue;
                        }
                        $products->addAll($product->getVariations());
                    }
                } catch (NotFound $exception) {
                    $products = null;
                }

                $csv->insertAll(
                    $this->mapper->stockCollectionToCsvArray($stock, $products)
                );
                $this->progressStorage->incrementProgress($progressKey, count($stock), $stock->getTotal());
            }
        } catch (NotFound $e) {
            // End of pagination. End progress to show we're done
            $this->endProgress($progressKey);
        }
    }

    protected function getHeaders()
    {
        return [
            "SKU",
            "Product Name",
            "Total Stock"
        ];
    }

    public function startProgress($progressKey)
    {
        $this->progressStorage->setProgress($progressKey, 0);
    }

    /**
     * @return int | null
     */
    public function checkProgress($progressKey)
    {
        $count = $this->progressStorage->getProgress($progressKey);
        if ($count === null) {
            return null;
        }
        return (int)$count;
    }

    protected function endProgress($progressKey)
    {
        $this->progressStorage->removeProgress($progressKey);
    }

    /**
     * @return int | null
     */
    public function getTotalForProgress($key)
    {
        $total = $this->progressStorage->getTotal($key);
        if ($total === null) {
            return null;
        }
        return (int)$total;
    }

    protected function notifyOfExport($userId)
    {
        $this->notifyIntercom(static::EVENT_STOCK_EXPORT, $userId);
    }

    protected function notifyOfUpload($userId)
    {
        $this->notifyIntercom(static::EVENT_STOCK_IMPORT, $userId);
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
}
