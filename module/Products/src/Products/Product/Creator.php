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
use CG\Stdlib\Exception\Runtime\ValidationException;
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
    protected $productMapper;
    /** @var Service */
    protected $productService;
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
        Mapper $productMapper,
        Service $service,
        ActiveUserInterface $activeUserContainer,
        DetailMapper $detailMapper,
        ImageService $imageService,
        StockCreator $stockCreator,
        LockingService $lockingService,
        ParentProductImporter $parentProductImporter,
        SimpleProductImporter $simpleProductImporter
    ) {
        $this->productMapper = $productMapper;
        $this->productService = $service;
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
        $this->validateUserInput($productData);

        $productData = $this->reformatSingleVariationAsSimpleProduct($productData);
        $productData = $this->addDefaultProductData($productData, 0);
        $this->addGlobalLogEventParams(['ou' => $productData['organisationUnitId'], 'sku' => $productData['sku']]);
        $this->logInfo('Starting Product creation for OU %d, SKU %s', [$productData['organisationUnitId'], $productData['sku']], [static::LOG_CODE, 'Starting']);
        $this->logDebugDump($productData, 'Creating from the following data', [], [static::LOG_CODE, 'RawData']);

        $variationsData = $this->splitOutVariationDataFromProductData($productData);
        $stockData = $this->splitOutStockDataFromProductData($productData);

        $product = $this->createProductWithVariationsAndStock($productData, $variationsData, $stockData);
        $savedProduct = $this->saveProduct($product);

        // Unfortunately when we create a new Entity no eTag is returned which we need, have to fetch
        $fetchedProduct = $this->productService->fetch($savedProduct->getId());
        $this->logInfo('Finished Product creation for OU %d, SKU %s', [$fetchedProduct->getOrganisationUnitId(), $fetchedProduct->getSku()], [static::LOG_CODE, 'Finished']);
        $this->removeGlobalLogEventParams(['ou', 'sku']);
        return $fetchedProduct;
    }

    protected function validateUserInput(array $productData)
    {
        if (!$this->isRequiredCreationDataFieldsPresent($productData)) {
            throw new ValidationException('Not all required fields were completed');
        }
        if ($this->isForExistingSku($productData)) {
            throw new ValidationException('You already have a product with that SKU');
        }
    }

    protected function isRequiredCreationDataFieldsPresent(array $productData): bool
    {
        if (!isset($productData['name']) || $productData['name'] == '') {
            return false;
        }
        $variationsData = ($this->hasVariations($productData) ? $productData['variations'] : [$productData]);
        foreach ($variationsData as $variationData) {
            if (!isset($variationData['sku'], $variationData['quantity']) ||
                $variationData['sku'] == '' || $variationData['quantity'] = ''
            ) {
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
            ->setOrganisationUnitId([$organisationUnitId])
            ->setSku($skus);
        return $this->productService->fetchCollectionByFilter($filter);
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
            $attributeNames = array_merge($attributeNames, array_keys($variationData['attributeValues']));
        }
        return array_unique($attributeNames);
    }

    protected function splitOutVariationDataFromProductData(array &$productData): array
    {
        $variationData = $productData['variations'] ?? [];
        unset($productData['variations']);
        return $variationData;
    }

    protected function splitOutStockDataFromProductData(array &$productData): array
    {
        $stockData = $productData['stock'] ?? [];
        $stockData['quantity'] = $productData['quantity'] ?? 0;
        unset($productData['stock'], $productData['quantity']);
        return $stockData;
    }

    protected function createProductWithVariationsAndStock(array $productData, array $variationsData, array $stockData): Product
    {
        $product = $this->productMapper->fromArray($productData);
        $images = $this->fetchImagesForProductData($productData);
        $product->setImages($images);
        $variations = $this->createVariationProducts($variationsData, $images);
        $product->setVariations($variations);
        $productDetail = $this->createProductDetail($productData);
        $product->setDetails($productDetail);
        $stock = $this->createStock($stockData, $product);
        $product->setStock($stock);

        return $product;
    }

    protected function fetchImagesForProductData(array $productData): ImageCollection
    {
        if (!isset($productData['imageIds']) || empty($productData['imageIds'])) {
            return new ImageCollection(Image::class, 'empty');
        }
        $filter = (new ImageFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId(array_column($productData['imageIds'], 'imageId'));
        return $this->imageService->fetchCollectionByPaginationAndFilters($filter);
    }

    protected function createVariationProducts(array $variationsData, ImageCollection $parentImages): ProductCollection
    {
        $variations = new ProductCollection(Product::class, 'newProductVariations');
        if (empty($variationsData)) {
            return $variations;
        }
        foreach ($variationsData as $variationData) {
            $variationData = $this->addDefaultProductData($variationData);
            $stockData = $this->splitOutStockDataFromProductData($variationData);
            $variation = $this->productMapper->fromArray($variationData);
            $images = $this->getImagesForVariation($variationData, $parentImages);
            $variation->setImages($images);
            $variationDetail = $this->createProductDetail($variationData);
            $variation->setDetails($variationDetail);
            $stock = $this->createStock($stockData, $variation);
            $variation->setStock($stock);
            $variation->setId($this->generateTemporaryId());
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

    protected function createProductDetail(array $detailData): ?ProductDetail
    {
        if (!isset($detailData['sku']) || $detailData['sku'] == '') {
            return null;
        }
        // Sometimes the dimensions come through as the empty string
        $filteredDetailData = array_filter($detailData);
        $filteredDetailData = $this->convertDimensionsForStorage($filteredDetailData);

        return $this->detailMapper->fromArray($filteredDetailData);
    }

    protected function convertDimensionsForStorage(array $detailData): array
    {
        $detailData['height'] = (isset($detailData['height']) ? $this->convertDimensionForStorage($detailData['height']) : null);
        $detailData['width'] = (isset($detailData['width']) ? $this->convertDimensionForStorage($detailData['width']) : null);
        $detailData['length'] = (isset($detailData['length']) ? $this->convertDimensionForStorage($detailData['length']) : null);
        return $detailData;
    }

    protected function convertDimensionForStorage(float $dimension): float
    {
        return ProductDetail::convertLength($dimension, ProductDetail::DISPLAY_UNIT_LENGTH, ProductDetail::UNIT_LENGTH);
    }

    protected function createStock(array $stockData, Product $product): ?Stock
    {
        if ($product->isParent()) {
            return null;
        }

        $stock = $this->stockCreator->create(
            $product->getOrganisationUnitId(),
            $product->getSku(),
            $stockData['quantity']
        );

        if (isset($stockData['stockMode']) && $stockData['stockMode'] != '') {
            $stock->setStockMode($stockData['stockMode']);
        }
        if (isset($stockData['stockLevel']) && $stockData['stockLevel'] != '') {
            $stock->setStockLevel($stockData['stockLevel']);
        }

        return $stock;
    }

    protected function generateTemporaryId(): float
    {
        return hexdec(uniqid("", true));
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