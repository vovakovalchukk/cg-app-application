<?php

namespace Products\Product\Link;

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

    const LOG_MSG_PRODUCT_NOT_FOUND_FOR_LINK = 'Product with sku <%s> was not loaded, but it was required as a link by product with sku <%s>';

    public function __construct(
        ProductService $productService,
        ProductLinkService $productLinkService,
        ProductMapper $productMapper,
        ProductLinkNodeService $productLinkNodeService
    ) {
        $this->productService = $productService;
        $this->productLinkService = $productLinkService;
        $this->productMapper = $productMapper;
        $this->productLinkNodeService = $productLinkNodeService;
    }

    public function getSkusProductCantLinkTo($ouId, $skuOfProduct): array
    {
        $productLinkId = ProductLink::generateId($ouId, $skuOfProduct);
        $linkNode = $this->productLinkNodeService->fetch($productLinkId);
        $link = $this->productLinkService->fetch($productLinkId);

        $skusProductCantLinkTo = [$skuOfProduct => true];
        foreach ($linkNode->getAncestors() as $ancestorSku) {
            $skusProductCantLinkTo[$ancestorSku] = true;
        }

        foreach ($link->getStockSkuMap() as $notIfCantBeLinkedFromSku => $quantity) {
            $skusProductCantLinkTo[$notIfCantBeLinkedFromSku] = true;
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
        $productsForSkus = $this->productService->fetchCollectionByOUAndSku([$ouId], $skusToFetchLinkedProductsFor);
        $productsForLinks = $this->fetchProductsForLinks($ouId, $productLinks);
        $parentProducts = $this->fetchParentProductsForVariations($ouId, $productsForLinks);

        $productLinksByProductId = [];
        /** @var Product $product */
        foreach ($productsForSkus as $product) {
            /** @var ProductLink $productLink */
            $productLink = $productLinks->getById(ProductLink::generateId($ouId, $product->getSku()));

            if (!$productLink) {
                continue;
            }

            foreach ($productLink->getStockSkuMap() as $stockSku => $stockQuantity) {
                $productLinkProduct = $this->getProductForLinkSku($productsForLinks, $parentProducts, $productLink->getProductSku(), $stockSku);

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
        string $productSkuOfLink,
        string $stockSku
    ): Product {
        $matchingProducts = $productsForLinks->getBy('sku', $stockSku);

        if (!$matchingProducts || count($matchingProducts) == 0) {
            $this->logCritical(
                static::LOG_MSG_PRODUCT_NOT_FOUND_FOR_LINK,
                [$stockSku, $productSkuOfLink]
            );
        }
        $productLinkProduct = $matchingProducts->getFirst();
        if ($productLinkProduct->isVariation()) {
            $productLinkProduct = $parentProductsForLinks->getById($productLinkProduct->getParentProductId());
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
            return $this->productService->fetchCollectionByOUAndSku([$ouId], $productLinkProductSkus);

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

        return $this->productService->fetchCollectionByOUAndId([$ouId], $idsToFetch);
    }
}