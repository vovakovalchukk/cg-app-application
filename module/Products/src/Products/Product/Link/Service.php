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
        $productsForLinks = $this->fetchProductsForLinks($ouId, $skusToFetchLinkedProductsFor, $productLinks);

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

//    protected function getMapOfParentProductsByProductId(ProductCollection $products, $)

    /**
     * @param ProductCollection $products
     * @return Product[]
     */
    protected function getProductsBySku(ProductCollection $products)
    {
        $productsBySku = [];

        /** @var Product $product */
        foreach ($products as $product) {
            if (!$product->isParent()) {
                $productsBySku[$product->getSku()] = $product;
                continue;
            }
            /** @var Product $variation */
            foreach ($product->getVariations() as $variation) {
                $productsBySku[$variation->getSku()] = $variation;
            }
        }

        return $productsBySku;
    }

    public function fetchProductsForLinks($ouId, $allVariationsBySkus, $productLinks): ProductCollection
    {
        $productLinkProducts = [];
        try {
            $productLinkProductSkus = [];
            foreach ($allVariationsBySkus as $sku) {
                $linkedProduct = $productLinks->getById(ProductLink::generateId($ouId, $sku));
                if ($linkedProduct) {
                    foreach ($linkedProduct->getStockSkuMap() as $stockSku => $stockQty) {
                        $productLinkProductSkus[] = $stockSku;
                    }
                }
            }
            $productLinkProducts = $this->productService->fetchCollectionByOUAndSku([$ouId], $productLinkProductSkus);

        } catch(NotFound $e) {
            //  no-op
        }
        return $productLinkProducts;
    }

    public function fetchParentProducts($ouId, $productSkus, $productLinkProducts): ProductCollection
    {
        $parentProducts = [];
        try {
            $parentProducts = $this->productService->fetchCollectionByOUAndSku([$ouId], array_keys($productSkus));
            foreach ($productLinkProducts as $product) {
                if ($product->getParentProductId() === 0) {
                    $parentProducts->attach($product);
                }
            }
        } catch(NotFound $e) {
            //  no-op
        }
        return $parentProducts;
    }
}