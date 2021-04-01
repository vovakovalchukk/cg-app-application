<?php

namespace Products\Product\Link;

use CG\Image\Service as ImageService;
use CG\Image\Filter as ImageFilter;
use CG\Image\Collection as Images;
use CG\Image\Entity as Image;
use CG\Product\Service\Service as ProductService;
use CG\Product\Link\Service as ProductLinkService;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Link\Collection as ProductLinkCollection;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\LinkNode\Service as ProductLinkNodeService;
use CG\Product\Entity as Product;
use CG\Product\Collection as ProductCollection;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Service implements LoggerAwareInterface
{
    use LogTrait;
    /** @var ProductService */
    protected $productService;
    /** @var ProductLinkService */
    protected $productLinkService;
    /** @var ProductMapper */
    protected $productMapper;
    /** @var ProductLinkNodeService */
    protected $productLinkNodeService;
    /** @var ImageService */
    protected $imageService;

    const LOG_CODE = 'ProductLinkService';
    const LOG_MSG_PRODUCT_NOT_FOUND_FOR_LINK = 'Product with sku <%s> was not loaded, but it was required as a link by product with sku <%s>';
    const LOG_MSG_PARENT_PRODUCT_NOT_IN_COLLECTION = 'Parent product for variation with sku <%s> was not loaded, fetching directly';

    public function __construct(
        ProductService $productService,
        ProductLinkService $productLinkService,
        ProductMapper $productMapper,
        ProductLinkNodeService $productLinkNodeService,
        ImageService $imageService
    ) {
        $this->productService = $productService;
        $this->productLinkService = $productLinkService;
        $this->productMapper = $productMapper;
        $this->productLinkNodeService = $productLinkNodeService;
        $this->imageService = $imageService;
    }

    public function getSkusProductCantLinkTo($ouId, $skuOfProduct): array
    {
        $productLinkId = ProductLink::generateId($ouId, $skuOfProduct);
        $skusProductCantLinkTo = [$skuOfProduct => true];

        try {
            $linkNode = $this->productLinkNodeService->fetch($productLinkId);
            foreach ($linkNode->getAncestors() as $ancestorSku) {
                $skusProductCantLinkTo[$ancestorSku] = true;
            }
        } catch (NotFound $exception) {
            //noop
        }

        try {
            $link = $this->productLinkService->fetch($productLinkId);
            foreach ($link->getStockSkuMap() as $notIfCantBeLinkedFromSku => $quantity) {
                $skusProductCantLinkTo[$notIfCantBeLinkedFromSku] = true;
            }
        } catch (NotFound $exception) {
            //noop
        }

        return $skusProductCantLinkTo;
    }

    public function fetchLinksForSkus($ouId, array $skus): ProductLinkCollection
    {
        return $this->productLinkService->fetchLinksForSkus($ouId, $skus);
    }

    public function fetch($id): ProductLink
    {
        return $this->productLinkService->fetch($id);
    }

    public function save(ProductLink $productLink)
    {
        return $this->productLinkService->save($productLink);
    }

    public function remove(ProductLink $productLink)
    {
        $this->productLinkService->remove($productLink);
    }

    public function getProductLinksByProductId($ouId, $skusToFetchLinkedProductsFor, ProductLinkCollection $productLinks)
    {
        $productsForSkus = $this->productService->fetchCollectionByOUAndSku([$ouId], $skusToFetchLinkedProductsFor, [Product::EMBEDDED_DATA_TYPE_NONE]);
        $productsForLinks = $this->fetchProductsForLinks($ouId, $productLinks);
        $parentProducts = $this->fetchParentProductsForVariations($ouId, $productsForLinks);
        $images = $this->fetchImages(
            ...$this->getPrimaryImageIds($productsForSkus),
            ...$this->getPrimaryImageIds($productsForLinks),
            ...$this->getPrimaryImageIds($parentProducts)
        );
        $productLinksByProductId = [];
        /** @var Product $product */
        foreach ($productsForSkus as $product) {
            /** @var ProductLink $productLink */
            $productLink = $productLinks->getById(ProductLink::generateId($ouId, $product->getSku()));

            if (!$productLink) {
                continue;
            }

            foreach ($productLink->getStockSkuMap() as $stockSku => $stockQuantity) {
                try {
                    $productLinkProduct = $this->getProductForLinkSku($productsForLinks, $parentProducts, $stockSku, $images);
                } catch (NotFound $exception) {
                    $this->logCriticalException($exception, static::LOG_MSG_PRODUCT_NOT_FOUND_FOR_LINK, [$stockSku, $productLink->getProductSku()], static::LOG_CODE);
                    continue;
                }
                $this->attachImageToProduct($productLinkProduct, $images);
                if ($product->getParentProductId() == 0) {
                    $productLinksByProductId[$product->getId()][$product->getId()][] = [
                        'sku' => $stockSku,
                        'quantity' => $stockQuantity,
                        'product' => $this->productMapper->getFullProductDataArray(
                            $productLinkProduct
                        )
                    ];
                } else {
                    $productLinksByProductId[$product->getParentProductId()][$product->getId()][] = [
                        'sku' => $stockSku,
                        'quantity' => $stockQuantity,
                        'product' => $this->productMapper->getFullProductDataArray(
                            $productLinkProduct
                        )
                    ];
                }
            }

        }

        return $productLinksByProductId;
    }

    protected function getProductForLinkSku(
        ProductCollection $productsForLinks,
        ProductCollection $parentProductsForLinks,
        string $stockSku
    ): Product {
        $matchingProducts = $productsForLinks->getBy('sku', $stockSku);
        if (!$matchingProducts || count($matchingProducts) == 0) {
            throw new NotFound(sprintf('Failed to find product sku <%s> in collection of products related to link', $stockSku));
        }
        $productLinkProduct = $matchingProducts->getFirst();
        if ($productLinkProduct->isVariation()) {
            $parentProductId = $productLinkProduct->getParentProductId();
            if ($parentProductsForLinks->containsId($parentProductId)) {
                $productLinkProduct = $parentProductsForLinks->getById($productLinkProduct->getParentProductId());
            } else {
                $this->logWarning(static::LOG_MSG_PARENT_PRODUCT_NOT_IN_COLLECTION, [$stockSku], static::LOG_CODE);
                $productLinkProduct = $this->productService->fetch($parentProductId);
            }
        }

        return $productLinkProduct;
    }

    public function fetchProductsForLinks($ouId, ProductLinkCollection $productLinks): ProductCollection
    {
        try {
            $productLinkProductSkus = [];
            /** @var ProductLink $productLink */
            foreach ($productLinks as $productLink) {
                foreach($productLink->getStockSkuMap() as $stockSku => $stockQty) {
                    $productLinkProductSkus[] = $stockSku;
                }
            }
            return $this->productService->fetchCollectionByOUAndSku([$ouId], $productLinkProductSkus, [Product::EMBEDDED_DATA_TYPE_NONE]);

        } catch(NotFound $e) {
            return new ProductCollection(Product::class, __FUNCTION__);
        }
    }

    public function fetchParentProductsForVariations($ouId, ProductCollection $productLinksProducts): ProductCollection
    {
        $idsToFetch = [];
        /** @var Product $product */
        foreach ($productLinksProducts as $product) {
            if ($product->isVariation()) {
                $idsToFetch[] = $product->getParentProductId();
            }
        }

        if (count($idsToFetch) == 0) {
            return new ProductCollection(Product::class, __FUNCTION__);
        }

        return $this->productService->fetchCollectionByOUAndId([$ouId], $idsToFetch, [Product::EMBEDDED_DATA_TYPE_NONE]);
    }

    protected function getPrimaryImageIds(ProductCollection $productCollection): \Generator
    {
        /** @var Product $product */
        foreach ($productCollection as $product) {
            try {
                yield $this->getPrimaryImageId($product);
            } catch (NotFound $e) {
                continue;
            }
        }
    }

    protected function getPrimaryImageId(Product $product): int
    {
        $imageIds = $product->getImageIds();
        if (!is_array($imageIds) || empty($imageIds)) {
            throw new NotFound();
        }
        $firstImageId = current($imageIds);
        if (is_array($firstImageId) && isset($firstImageId['id'])) {
            return $firstImageId['id'];
        }
        $this->logDebugDump([$firstImageId, $imageIds], 'Bad first image ID', [], 'Oliver');
        throw new NotFound();
    }

    protected function fetchImages(int ...$ids): Images
    {
        try {
            return $this->imageService->fetchCollectionByPaginationAndFilters((new ImageFilter('all', 1))->setId(array_unique($ids)));
        } catch (NotFound $e) {
            return new Images(Image::class, __METHOD__);
        }
    }

    protected function attachImageToProduct(Product $product, Images $images): void
    {
        try {
            $primaryImage = $images->getById($this->getPrimaryImageId($product));
        } catch (NotFound $e) {
            return;
        }
        if ($primaryImage === null) {
            return;
        }
        $imagesToAttach = new Images(Image::class, __METHOD__);
        $imagesToAttach->attach($primaryImage);
        $product->setImages($imagesToAttach);
    }
}