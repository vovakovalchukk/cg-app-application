<?php

namespace Products\Product\Link;

use CG\Product\Service\Service as ProductService;
use CG\Product\Link\Service as ProductLinkService;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Link\Collection as ProductLinkCollection;
use CG\Product\Link\Entity as ProductLink;
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

    public function getProductLinksByProductId($ouId, $productsById, $allProductsBySkus, ProductLinkCollection $productLinks)
    {
        $productLinkProducts = $this->fetchProductLinksForProducts($ouId, $allProductsBySkus, $productLinks);
        $parentProducts = $this->fetchParentProducts($ouId, $productsById, $productLinkProducts);
        $productLinksByProductId = [];
        foreach ($allProductsBySkus as $sku => $product) {
            $productLinkId = $product['organisationUnitId'].'-'.$sku;
            $linkedProduct = $productLinks->getById($productLinkId);
            if (! $linkedProduct) {
                continue;
            }
            foreach ($linkedProduct->getStockSkuMap() as $stockSku => $stockQty) {
                $matchingProductLinkProducts = $productLinkProducts->getBy('sku', $stockSku);
                if (count($matchingProductLinkProducts)) {
                    $matchingProductLinkProducts->rewind();
                    $productLinkProduct = $matchingProductLinkProducts->current();
                }
                if ($productLinkProduct) {
                    $id = $productLinkProduct->getParentProductId() > 0 ? $productLinkProduct->getParentProductId() : $productLinkProduct->getId();
                    $parentProduct = $parentProducts->getById($id);
                }
                /**
                 * instead of getting parent product of variation, need to get parent product of $stockSku
                 */
                $parentProductId = $product['parentProductId'];
                $variationProductId = $product['id'];
                if ($parentProductId == 0) {
                    $parentProductId = $product['id'];
                    $variationProductId = $product['id'];
                }

                $productLinksByProductId[$parentProductId][$variationProductId][] = [
                    'sku' => $stockSku,
                    'quantity' => $stockQty,
                    'product' => $parentProduct ? $this->productMapper->getFullProductDataArray($parentProduct) : null,
                ];
            }
        }
        return $productLinksByProductId;
    }


    public function fetchProductLinksForProducts($ouId, $allVariationsBySkus, $productLinks)
    {
        $productLinkProducts = [];
        try {
            $productLinkProductSkus = [];
            foreach ($allVariationsBySkus as $sku => $variation) {
                $linkedProduct = $productLinks->getById($variation['organisationUnitId'] . '-' . $sku);
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

    public function fetchParentProducts($ouId, $productIds, $productLinkProducts)
    {
        $parentProducts = [];
        try {
            $parentProducts = $this->productService->fetchCollectionByOUAndId([$ouId], array_keys($productIds));
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