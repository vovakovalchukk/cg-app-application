<?php

namespace Products\Product\Link;

use CG\Product\Service\Service as ProductService;
use CG\Product\Link\Service as ProductLinkService;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Link\Collection as ProductLinkCollection;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Entity as Product;
use CG\Product\Collection as ProductCollection;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    /** @var ProductService */
    protected $productService;
    /** @var ProductLinkService */
    protected $productLinkService;
    /** @var ProductMapper */
    protected $productMapper;

    public function __construct(
        ProductService $productService,
        ProductLinkService $productLinkService,
        ProductMapper $productMapper
    ) {
        $this->productService = $productService;
        $this->productLinkService = $productLinkService;
        $this->productMapper = $productMapper;
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
        $parentProducts = $this->fetchParentProducts($ouId, $productsForLinks);

        $productLinksByProductId = [];
        /** @var Product $product */
        foreach ($productsForSkus as $product) {
            /** @var ProductLink $productLink */
            $productLink = $productLinks->getById(ProductLink::generateId($ouId, $product->getSku()));

            if (!$productLink) {
                continue;
            }

            foreach ($productLink->getStockSkuMap() as $stockSku => $stockQuantity) {

                $matchingProducts = $productsForLinks->getBy('sku', $stockSku);
                foreach ($matchingProducts as $matchingProduct) {
                    $productLinkProduct = $matchingProduct;
                    break;
                }
                if ($productLinkProduct->getParentProductId()) {
                    $productLinkProduct = $parentProducts->getById($productLinkProduct->getParentProductId());
                }

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

    public function fetchParentProducts($ouId, ProductCollection $productLinksProducts): ProductCollection
    {
        $idsToFetch = [];
        /** @var Product $product */
        foreach ($productLinksProducts as $product) {
            if (!$product->isParent()) {
                $idsToFetch[] = $product->getParentProductId();
            }
        }

        if (count($idsToFetch) == 0) {
            return new ProductCollection(Product::class, __FUNCTION__);
        }

        return $this->productService->fetchCollectionByOUAndId([$ouId], $idsToFetch);
    }
}