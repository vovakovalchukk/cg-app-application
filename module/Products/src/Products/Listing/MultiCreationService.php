<?php
namespace Products\Listing;

use CG\Product\Client\Service as ProductService;
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

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
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

            $variations = $productData['variations'] ?? [];
            if (empty($variations)) {
                $this->logWarning(static::LOG_MSG_NO_VARIATIONS_SPECIFIED, [], static::LOG_CODE_NO_VARIATIONS_SPECIFIED);
                return false;
            }

            if ($this->isSimpleListing($product, $variations)) {
                return $this->createSimpleListings(
                    $accountIds,
                    $categoryTemplateIds,
                    $siteId,
                    $product,
                    $productData,
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
        string $guid
    ): bool {
        return false;
    }

    protected function createVariationeListings(
        array $accountIds,
        array $categoryTemplateIds,
        string $siteId,
        Product $product,
        array $productData,
        string $guid
    ): bool {
        return false;
    }
}