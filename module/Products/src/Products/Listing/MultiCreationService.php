<?php
namespace Products\Listing;

use CG\Channel\Listing\Import\ProductDetail\Importer as ProductDetailImporter;
use CG\Product\Client\Service as ProductService;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Entity as Product;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class MultiCreationService implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_MISSING_PRODUCT_ID = 'No product id specified - can\'t create listings';
    const LOG_MSG_MISSING_PRODUCT_ID = 'No product id specified - can\'t create listings';
    const LOG_CODE_REQUESTED_PRODUCT_NOT_FOUND = 'Product not found - can\'t create listings';
    const LOG_MSG_REQUESTED_PRODUCT_NOT_FOUND = 'Product %d not found - can\'t create listings';
    const LOG_CODE_NO_VARIATIONS_SPECIFIED = 'No variaitions specified - can\'t create listings';
    const LOG_MSG_NO_VARIATIONS_SPECIFIED = 'No variaitions specified - can\'t create listings';
    const LOG_CODE_MULTIPLE_VARIATIONS = 'Multiple variations specified - assuming variation listing';
    const LOG_MSG_MULTIPLE_VARIATIONS = '%d variations specified - assuming variation listing';
    const LOG_CODE_VARIATION_SKU_MATCH = 'Single variation specified with same sku as product - assuming simple listing';
    const LOG_MSG_VARIATION_SKU_MATCH = 'Single variation specified with same sku (%s) as product - assuming simple listing';
    const LOG_CODE_VARIATION_SKU_DIFFERS = 'Single variation specified with different sku from product - assuming variation listing';
    const LOG_MSG_VARIATION_SKU_DIFFERS = 'Single variation specified with different sku (%s) from product - assuming variation listing';
    const LOG_CODE_VARIATION_LISTING_INCOMPATABLE = 'Product is not a parent product but variation listing requested - can\'t create listings';
    const LOG_MSG_VARIATION_LISTING_INCOMPATABLE = 'Product %d is not a parent product but variation listing requested - can\'t create listings';

    /** @var ProductService */
    protected $productService;
    /** @var ProductDetailMapper */
    protected $productDetailMapper;
    /** @var ProductDetailImporter */
    protected $productDetailImporter;

    public function __construct(
        ProductService $productService,
        ProductDetailMapper $productDetailMapper,
        ProductDetailImporter $productDetailImporter
    ) {
        $this->productService = $productService;
        $this->productDetailMapper = $productDetailMapper;
        $this->productDetailImporter = $productDetailImporter;
    }

    public function createListings(
        array $accountIds,
        array $categoryTemplateIds,
        string $siteId,
        array $productData,
        &$guid = null
    ): bool {
        $this->addGlobalLogEventParam('guid', $guid = uniqid('', true));
        try {
            $productId = $productData['id'] ?? null;
            if (!$productId) {
                $this->logWarning(static::LOG_MSG_MISSING_PRODUCT_ID, [], static::LOG_CODE_MISSING_PRODUCT_ID);
                return false;
            }
            $this->addGlobalLogEventParam('product', $productId);

            try {
                /** @var Product $product */
                $product = $this->productService->fetch($productId);
            } catch (NotFound $exception) {
                $this->logWarningException($exception, static::LOG_MSG_REQUESTED_PRODUCT_NOT_FOUND, [$productId], static::LOG_CODE_REQUESTED_PRODUCT_NOT_FOUND);
                return false;
            }

            $variationsData = $productData['variations'] ?? [];
            if (empty($variationsData)) {
                $this->logWarning(static::LOG_MSG_NO_VARIATIONS_SPECIFIED, [], static::LOG_CODE_NO_VARIATIONS_SPECIFIED);
                return false;
            }

            if ($this->isSimpleListing($product, $variationsData)) {
                return $this->createSimpleListings(
                    $accountIds,
                    $categoryTemplateIds,
                    $siteId,
                    $product,
                    $productData,
                    reset($variationsData),
                    $guid
                );
            }

            if (!$product->isParent()) {
                $this->logWarning(static::LOG_MSG_VARIATION_LISTING_INCOMPATABLE, [$productId], static::LOG_CODE_VARIATION_LISTING_INCOMPATABLE);
                return false;
            }

            return $this->createVariationeListings(
                $accountIds,
                $categoryTemplateIds,
                $siteId,
                $product,
                $productData,
                $variationsData,
                $guid
            );
        } finally {
            $this->removeGlobalLogEventParams(['guid', 'product']);
        }
    }

    protected function isSimpleListing(Product $product, array $variations): bool
    {
        if (($count = count($variations)) != 1) {
            $this->logDebug(static::LOG_MSG_MULTIPLE_VARIATIONS, [$count], static::LOG_CODE_MULTIPLE_VARIATIONS);
            return false;
        }

        $sku = reset($variations)['sku'] ?? '';
        if ($product->getSku() == $sku) {
            $this->logDebug(static::LOG_MSG_VARIATION_SKU_MATCH, ['sku' => $sku], static::LOG_CODE_VARIATION_SKU_MATCH);
            return true;
        } else {
            $this->logDebug(static::LOG_MSG_VARIATION_SKU_DIFFERS, ['sku' => $sku], static::LOG_CODE_VARIATION_SKU_DIFFERS);
            return false;
        }
    }

    protected function createSimpleListings(
        array $accountIds,
        array $categoryTemplateIds,
        string $siteId,
        Product $product,
        array $productData,
        array $variationData,
        string $guid
    ): bool {
        $this->addGlobalLogEventParam('sku', $product->getSku());
        try {
            $this->updateSimpleProductDetail($product, $productData, $variationData);
            return false;
        } finally {
            $this->removeGlobalLogEventParam('sku');
        }
    }

    protected function createVariationeListings(
        array $accountIds,
        array $categoryTemplateIds,
        string $siteId,
        Product $product,
        array $productData,
        array $variationsData,
        string $guid
    ): bool {
        $skus = array_filter(array_map(function(array $variationData) {
            return $variationData['sku'] ?? null;
        }, $variationsData));
        $this->addGlobalLogEventParam('sku', implode(', ', $skus));

        try {
            $this->updateVariationsProductDetail($product, $productData, $variationsData);
            return false;
        } finally {
            $this->removeGlobalLogEventParam('sku');
        }
    }

    protected function mapProductDetails(int $ou, string $sku, array $productData, array $variationData): ProductDetail
    {
        return $this->productDetailMapper->fromArray([
            'organisationUnitId' => $ou,
            'sku' => $sku,
            'weight' => $variationData['weight'] ?? $productData['weight'] ?? null,
            'width' => $variationData['width'] ?? $productData['width'] ?? null,
            'height' => $variationData['height'] ?? $productData['height'] ?? null,
            'length' => $variationData['length'] ?? $productData['length'] ?? null,
            'description' => $variationData['description'] ?? $productData['description'] ?? null,
            'ean' => $variationData['ean'] ?? $productData['ean'] ?? null,
            'brand' => $variationData['brand'] ?? $productData['brand'] ?? null,
            'mpn' => $variationData['mpn'] ?? $productData['mpn'] ?? null,
            'asin' => $variationData['asin'] ?? $productData['asin'] ?? null,
            'price' => $variationData['price'] ?? $productData['price'] ?? null,
            'cost' => $variationData['cost'] ?? $productData['cost'] ?? null,
            'condition' => $variationData['condition'] ?? $productData['condition'] ?? null,
            'categoryTemplateIds' => $variationData['categoryTemplateIds'] ?? $productData['categoryTemplateIds'] ?? [],
            'upc' => $variationData['upc'] ?? $productData['upc'] ?? null,
            'isbn' => $variationData['isbn'] ?? $productData['isbn'] ?? null,
        ]);
    }

    protected function updateSimpleProductDetail(Product $product, array $productData, array $variationData)
    {
        $this->productDetailImporter->import(
            $product,
            $this->mapProductDetails(
                $product->getOrganisationUnitId(),
                $product->getSku(),
                $productData,
                $variationData
            )
        );
    }

    protected function updateVariationsProductDetail(Product $product, array $productData, array $variationsData)
    {
        $productDetails = [];
        foreach ($variationsData as $variationData) {
            $sku = $variationData['sku'] ?? null;
            if (!$sku) {
                continue;
            }
            $productDetails[$sku] = $this->mapProductDetails(
                $product->getOrganisationUnitId(),
                $sku,
                $productData,
                $variationData
            );
        }

        /** @var Product $variation */
        foreach ($product->getVariations() as $variation) {
            if (isset($productDetails[$variation->getSku()])) {
                $this->productDetailImporter->import($variation, $productDetails);
            }
        }
    }
}