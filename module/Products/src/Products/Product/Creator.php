<?php
namespace Products\Product;

use CG\Channel\Listing\Import\ParentProduct\Importer as ParentProductImporter;
use CG\Channel\Listing\Import\SimpleProduct\Importer as SimpleProductImporter;
use CG\Image\Collection as ImageCollection;
use CG\Image\Entity as Image;
use CG\Image\Filter as ImageFilter;
use CG\Image\Service as ImageService;
use CG\Locking\Service as LockingService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Mapper as DetailMapper;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Product\Mapper;
use CG\Product\Client\Service;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Entity as Stock;
use CG\Stock\Locking\Creator as StockCreator;
use CG\User\ActiveUserInterface;

class Creator implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'ProductCreator';

    /** @var Mapper */
    protected $mapper;
    /** @var Service */
    protected $service;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var DetailMapper */
    protected $detailMapper;
    /** @var ImageService */
    protected $imageService;
    /** @var StockCreator */
    protected $stockCreator;
    /** @var LockingService */
    protected $lockingService;
    /** @var ParentProductImporter */
    protected $parentProductImporter;
    /** @var SimpleProductImporter */
    protected $simpleProductImporter;

    public function __construct(
        Mapper $mapper,
        Service $service,
        ActiveUserInterface $activeUserContainer,
        DetailMapper $detailMapper,
        ImageService $imageService,
        StockCreator $stockCreator,
        LockingService $lockingService,
        ParentProductImporter $parentProductImporter,
        SimpleProductImporter $simpleProductImporter
    ) {
        $this->mapper = $mapper;
        $this->service = $service;
        $this->activeUserContainer = $activeUserContainer;
        $this->detailMapper = $detailMapper;
        $this->imageService = $imageService;
        $this->stockCreator = $stockCreator;
        $this->lockingService = $lockingService;
        $this->parentProductImporter = $parentProductImporter;
        $this->simpleProductImporter = $simpleProductImporter;
    }

    public function createFromUserInput(array $productData): Product
    {
        if (!$this->isRequiredCreationDataFieldsPresent($productData)) {
            throw new \InvalidArgumentException('Not all required fields were completed');
        }
        if ($this->isForExistingSku($productData)) {
            throw new \InvalidArgumentException('You already have a product with that SKU');
        }

        $productData = $this->reformatSingleVariationAsSimpleProduct($productData);
        $productData = $this->addDefaultProductData($productData, 0);
        $this->addGlobalLogEventParams(['ou' => $productData['organisationUnitId'], 'sku' => $productData['sku']]);
        $this->logInfo('Starting Product creation for OU %d, SKU %s', [$productData['organisationUnitId'], $productData['sku']], [static::LOG_CODE, 'Starting']);
        $this->logDebugDump($productData, 'Creating from the following data', [], [static::LOG_CODE, 'RawData']);

        $product = $this->createProduct($productData);
        $images = $this->fetchImagesForProductData($productData);
        $product->setImages($images);
        $variations = $this->createVariationProducts($productData, $images);
        $product->setVariations($variations);
        $productDetail = $this->createProductDetail($productData);
        $product->setDetails($productDetail);
        $stock = $this->createStock($productData);
        $product->setStock($stock);

        $savedProduct = $this->saveProduct($product);

        // Unfortunately when we create a new Entity no eTag is returned which we need, have to fetch
        $fetchedProduct = $this->service->fetch($savedProduct->getId());
        $this->logInfo('Finished Product creation for OU %d, SKU %s', [$fetchedProduct->getOrganisationUnitId, $fetchedProduct->getSku()], [static::LOG_CODE, 'Finished']);
        $this->removeGlobalLogEventParams(['ou', 'sku']);
        return $fetchedProduct;
    }

    protected function isRequiredCreationDataFieldsPresent(array $productData): bool
    {
        if (!isset($productData['name'])) {
            return false;
        }
        $variationsData = ($this->hasVariations($productData) ? $productData['variations'] : [$productData]);
        foreach ($variationsData as $variationData) {
            if (!isset($variationData['sku'], $variationData['quantity'])) {
                return false;
            }
        }
        return true;
    }

    protected function isForExistingSku(array $productData): bool
    {
        if ($this->hasVariations($productData)) {
            $skus = array_column($productData['variations'], 'sku');
        } else {
            $skus = [$productData['sku']];
        }
        try {
            $this->fetchProductsBySku($skus);
            return true;
        } catch (NotFound $e) {
            return false;
        }
    }

    protected function fetchProductsBySku(array $skus): ProductCollection
    {
        $organisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $filter = (new ProductFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($organisationUnitId)
            ->setSku($skus);
        return $this->service->fetchCollectionByFilter($filter);
    }

    protected function reformatSingleVariationAsSimpleProduct(array $productData): array
    {
        if (!isset($productData['variations']) || count($productData['variations']) > 1) {
            return $productData;
        }
        $variationData = $productData['variations'][0];
        if (isset($variationData['attributeValues']) && !empty($variationData['attributeValues'])) {
            return $productData;
        }
        // If there's only one variation and it doesn't have attributeValues we'll treat it as a simple product
        $reformattedData = $productData;
        unset($reformattedData['variations']);
        $reformattedData = array_merge($reformattedData, $variationData);
        return $reformattedData;
    }

    protected function hasVariations(array $productData): bool
    {
        return isset($productData['variations']) && !empty($productData['variations']);
    }

    protected function addDefaultProductData(array $productData, ?int $parentProductId = null): array
    {
        $productData['organisationUnitId'] = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $productData['parentProductId'] = $parentProductId;
        $productData['deleted'] = false;
        if ($this->hasVariations($productData)) {
            $productData['sku'] = '';
            $productData['attributeNames'] = $this->getAttributeNamesFromProductData($productData);
        }
        return $productData;
    }

    protected function getAttributeNamesFromProductData(array $productData): array
    {
        $attributeNames = [];
        foreach ($productData['variations'] as $variationData) {
            if (!isset($variationData['attributeValues'])) {
                continue;
            }
            $attributeNames = array_merge($productData['attributeNames'], array_keys($variationData['attributeValues']));
        }
        return array_unique($attributeNames);
    }

    protected function createProduct(array $productData): Product
    {
        return $this->mapper->fromArray($productData);
    }

    protected function fetchImagesForProductData(array $productData): ImageCollection
    {
        if (!isset($productData['imageIds']) || empty($productData['imageIds'])) {
            return new ImageCollection(Image::class, 'empty');
        }
        $filter = (new ImageFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($productData['imageIds']);
        return $this->imageService->fetchCollectionByPaginationAndFilters($filter);
    }

    protected function createVariationProducts(array $productData, ImageCollection $parentImages): ProductCollection
    {
        if (!$this->hasVariations($productData)) {
            return null;
        }
        $variations = new ProductCollection(Product::class, 'newProductVariations');
        foreach ($productData['variations'] as $variationData) {
            $variationData = $this->addDefaultProductData($variationData);
            $variation = $this->createProduct($variationData);
            $images = $this->getImagesForVariation($variationData, $parentImages);
            $variation->setImages($images);
            $variationDetail = $this->createProductDetail($variationData);
            $variation->setDetails($variationDetail);
            $stock = $this->createStock($variationData, $variation);
            $variation->setStock($stock);
            $variations->attach($variation);
        }
        return $variations;
    }

    protected function getImagesForVariation(array $variationData, ImageCollection $parentImages): ImageCollection
    {
        $images = new ImageCollection(Image::class, 'newVariationImages');
        // We currently only expect one image per variation. This may change in future.
        if (!isset($variationData['imageId']) || !$parentImages->containsId($variationData['imageId'])) {
            return $images;
        }
        $image = $parentImages->getById($variationData['imageId']);
        $images->attach($image);
        return $images;
    }

    protected function createProductDetail(array $detailData): ProductDetail
    {
        return $this->detailMapper->fromArray($detailData);
    }

    protected function createStock(array $productData, Product $product): Stock
    {
        if ($this->hasVariations($productData)) {
            return null;
        }

        $stock = $this->stockCreator->create(
            $product->getOrganisationUnitId(),
            $product->getSku(),
            $productData['quantity']
        );

        if (isset($productData['stock'], $productData['stock']['stockMode'])) {
            $stock->setStockMode($productData['stock']['stockMode']);
        }
        if (isset($productData['stock'], $productData['stock']['stockLevel'])) {
            $stock->setStockLevel($productData['stock']['stockLevel']);
        }

        return $stock;
    }

    protected function saveProduct(Product $product): Product
    {
        try {
            $lock = $this->lockingService->lock($product);

            if ($product->isParent()) {
                return $this->parentProductImporter->import($product, null, $product->getDetails());
            } else {
                return $this->simpleProductImporter->import($product, null, $product->getDetails());
            }

        } finally {
            if (isset($lock)) {
                $this->lockingService->unlock($lock);
            }
        }
    }
}