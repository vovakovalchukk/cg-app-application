<?php
namespace Products\Product\Csv;

use CG\Image\Entity as Image;
use CG\Listing\Client\Service as ListingService;
use CG\Listing\Entity as Listing;
use CG\Listing\Filter as ListingFilter;
use CG\Location\Service as LocationService;
use CG\Location\Type as LocationType;
use CG\Product\Client\Service as ProductService;
use CG\Product\Entity as Product;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Service as StockLocationService;
use CG\User\ActiveUserInterface;
use League\Csv\Writer as CsvWriter;

class Service
{
    const MIME_TYPE = 'text/csv';
    const FILE_NAME = 'products.csv';

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

    public function __construct(
        ListingService $listingService,
        LocationService $locationService,
        ActiveUserInterface $activeUserContainer,
        StockLocationService $stockLocationService,
        ProductService $productService
    ) {
        $this->listingService = $listingService;
        $this->locationService = $locationService;
        $this->activeUserContainer = $activeUserContainer;
        $this->stockLocationService = $stockLocationService;
        $this->productService = $productService;
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
            $file->addLine(
                (new Line())
                    ->setName($product->getName())
                    ->setDescription($product->getDetails()->getDescription())
                    ->setPrice($product->getDetails()->getPrice())
                    ->setCondition($product->getDetails()->getCondition())
                    ->setImage($this->getProductImage($product))
                    ->setStock($this->getStockForProduct($product, $merchantLocationIds))
            );
        }
        return $this->resultsToCsv($file);
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
        $stock = 0;
        if ($product->getStock()) {
            $stockLocations = $this->stockLocationService
                ->getFromCollectionByLocationIds($product->getStock()->getLocations(), $merchantLocationIds);
            /** @var StockLocation $stockLocation */
            foreach ($stockLocations as $stockLocation) {
                $stock += $stockLocation->getOnHand();
            }
        }
        return $stock;
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
