<?php
namespace Products\Csv\Stock;

use CG\Intercom\Event\Service as IntercomEventService;
use CG\Product\Client\Service as ProductService;
use CG\Stock\Gearman\Generator\StockImport as StockImportGearmanJobGenerator;
use CG\Stock\Import\File\Mapper as ImportFileMapper;
use CG\Stock\Import\File\StorageInterface as ImportFileStorage;
use CG\Stock\StorageInterface as StockStorage;
use CG\User\ActiveUserInterface;
use League\Csv\Writer as CsvWriter;
use Products\Csv\ServiceAbstract;
use SplFileObject as Csv;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service extends ServiceAbstract
{
    const FILENAME = "stock.csv";
    const EVENT_TYPE = "Stock Levels";

    /** @var StockStorage $stockStorage */
    protected $stockStorage;
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

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        StockStorage $stockStorage,
        ProductService $productService,
        Mapper $mapper,
        ImportFileStorage $importFileStorage,
        ImportFileMapper $importFileMapper,
        StockImportGearmanJobGenerator $stockImportGearmanJobGenerator,
        IntercomEventService $intercomEventService,
        ProgressStorage $progressStorage
    ) {
        parent::__construct(
            $activeUserContainer,
            $intercomEventService,
            $progressStorage
        );
        $this->stockStorage = $stockStorage;
        $this->productService = $productService;
        $this->mapper = $mapper;
        $this->importFileStorage = $importFileStorage;
        $this->importFileMapper = $importFileMapper;
        $this->stockImportGearmanJobGenerator = $stockImportGearmanJobGenerator;
        $this->intercomEventService = $intercomEventService;
        $this->progressStorage = $progressStorage;
    }

    protected function generateCsv($userId, $organisationUnitId, $progressKey = null): CsvWriter
    {
        $this->notifyIntercom(static::EVENT_TYPE . static::EVENT_EXPORTED, $userId);

        $csv = CsvWriter::createFromFileObject(new \SplTempFileObject(-1));
        $csv->insertOne($this->getHeaders());

        $this->applyStockValuesToCsv($csv, $organisationUnitId, $progressKey);

        return $csv;
    }

    protected function applyStockValuesToCsv(CsvWriter $csv, $organisationUnitId, $progressKey = null)
    {
        try {
            $page = 1;
            $merchantLocationIds = $this->mapper->getMerchantLocationIds($organisationUnitId);
            while (true) {
                /** @var Stock $stock */
                $stock = $this->stockStorage->fetchCollectionByPaginationAndFilters(
                    static::COLLECTION_SIZE,
                    $page++,
                    [],
                    [$organisationUnitId],
                    null,
                    $merchantLocationIds
                );

                try {
                    $products = $this->productService->fetchCollectionByFilter(
                        (new ProductFilter('all'))
                            ->setReplaceVariationWithParent(true)
                            ->setOrganisationUnitId([$organisationUnitId])
                            ->setSku(array_values($stock->getArrayOf('sku')))
                            ->setEmbeddedDataToReturn([Product::EMBEDDED_DATA_TYPE_VARIATION])
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
                    $this->mapper->stockCollectionToCsvArray($stock, $products, $merchantLocationIds)
                );
                $this->progressStorage->incrementProgress($progressKey, count($stock), $stock->getTotal());
            }
        } catch (NotFound $e) {
            // End of pagination. End progress to show we're done
            $this->endProgress($progressKey);
        }
    }

    protected function getHeaders(): array
    {
        return [
            "SKU",
            "Product Name",
            "Total Stock"
        ];
    }

    protected function uploadCsv(
        $userId,
        $organisationUnitId,
        $updateOption,
        $fileContents
    ) {
        $this->notifyIntercom(static::EVENT_TYPE . self::EVENT_IMPORTED, $userId);
        ($this->stockImportGearmanJobGenerator)->generateJob(
            $this->saveFile($updateOption, $fileContents, $userId, $organisationUnitId)
        );
    }

    protected function saveFile($updateOption, $fileContents, $userId, $organisationUnitId)
    {
        return $this->importFileStorage->save(
            $this->importFileMapper->fromUpload($updateOption, $fileContents, $userId, $organisationUnitId)
        );
    }
}
