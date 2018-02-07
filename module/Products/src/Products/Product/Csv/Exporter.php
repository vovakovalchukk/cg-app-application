<?php
namespace Products\Product\Csv;

use CG\Image\Entity as Image;
use CG\Listing\Client\Service as ListingService;
use CG\Listing\Entity as Listing;
use CG\Listing\Filter as ListingFilter;
use CG\Location\Service as LocationService;
use CG\Location\Type as LocationType;
use CG\Product\Client\Service as ProductService;
use CG\Product\Detail\Entity as ProductDetails;
use CG\Product\Entity as Product;
use CG\Product\Listing\Line\Mapper as CsvLineMapper;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Location\Collection as StockLocationCollection;
use CG\Stock\Location\Service as StockLocationService;
use CG\User\ActiveUserInterface;
use League\Csv\Writer as CsvWriter;

class Exporter
{
    const MIME_TYPE = 'text/csv';
    const FILE_NAME = 'products_%s_%s.csv';

    /** @var ListingService */
    protected $listingService;
    /** @var LocationService */
    protected $locationService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var StockLocationService */
    protected $stockLocationService;
    /** @var ProductService */
    protected $productService;
    /** @var CsvLineMapper */
    protected $csvLineMapper;

    public function __construct(
        ListingService $listingService,
        LocationService $locationService,
        ActiveUserInterface $activeUserContainer,
        StockLocationService $stockLocationService,
        ProductService $productService,
        CsvLineMapper $csvLineMapper
    ) {
        $this->listingService = $listingService;
        $this->locationService = $locationService;
        $this->activeUserContainer = $activeUserContainer;
        $this->stockLocationService = $stockLocationService;
        $this->productService = $productService;
        $this->csvLineMapper = $csvLineMapper;
    }

    public function exportToCsv(string $channel): CsvWriter
    {
        $merchantLocationIds = $this->fetchMerchantLocationIds();
        $ouIds = $this->activeUserContainer->getActiveUser()->getOuList();
        $file = new File();
        /** @var Listing $listing */
        foreach ($this->fetchListings($ouIds, $channel) as $listing) {
            if (!$this->isValidListingForExport($listing)) {
                continue;
            }
            /** @var Product $product */
            $product = $this->productService->fetch($listing->getProductIds()[0]);
            $detail = $product->getDetails() ?? new ProductDetails($product->getOrganisationUnitId(), $product->getSku());
            $file->addLine(
                $this->csvLineMapper->createFromProductAndDetails(
                    $product,
                    $detail,
                    $this->getProductImage($product),
                    $this->getStockForProduct($product, $merchantLocationIds)
                )
            );
        }
        return $this->resultsToCsv($file);
    }

    public function getFileName(string $channel)
    {
        return sprintf(static::FILE_NAME, $channel, (new DateTime())->format('Ymd_His'));
    }

    protected function fetchListings(array $ouIds, string $channel)
    {
        return $this->listingService->fetchCollectionByFilter(
            (new ListingFilter('all', 1))
            ->setOrganisationUnitId($ouIds)
            ->setChannel([$channel])
        );
    }

    protected function isValidListingForExport(Listing $listing)
    {
        if (count($listing->getProductIds()) === 1) {
            return true;
        }
        return false;
    }

    protected function fetchMerchantLocationIds(): array
    {
        return $this->locationService->fetchIdsByType(
            [LocationType::MERCHANT],
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
        );
    }

    protected function getStockForProduct(Product $product, array $merchantLocationIds): int
    {
        if (!$product->getStock()) {
            return 0;
        }
        try {
            /** @var StockLocationCollection $stockLocations */
            $stockLocations = $this->stockLocationService->getFromCollectionByLocationIds(
                $product->getStock()->getLocations(),
                $merchantLocationIds
            );
            return $stockLocations->getTotalOnHand();
        } catch (NotFound $e) {
            return 0;
        }
    }

    protected function getProductImage(Product $product): string
    {
        if (count($product->getImages()) > 0) {
            /** @var Image $image */
            $image = $product->getImages()->getFirst();
            return $image->getUrl();
        }
        return '';
    }

    protected function resultsToCsv(File $file): CsvWriter
    {
        $csv = CsvWriter::createFromFileObject(new \SplTempFileObject());
        $csv->insertAll($file->toArray());
        return $csv;
    }
}
