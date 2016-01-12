<?php
namespace Products\Stock\Log;

use CG\Product\Client\Service as ProductService;
use CG\Product\Entity as Product;

class Service
{
    const DEFAULT_IMAGE_URL = '/noproductsimage.png';

    /** @var ProductService */
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->setProductService($productService);
    }

    public function getProductDetails($productId)
    {
        $product = $this->productService->fetch($productId);
        $nameProduct = $skuProduct = $imageProduct = $product;

        if ($product->isVariation()) {
            $parentProduct = $this->productService->fetch($product->getParentProductId());
            $nameProduct = $imageProduct = $parentProduct;
        } else if ($product->isParent()) {
            $variations = $product->getVariations();
            $variations->rewind();
            $skuProduct = $variations->current();
        }

        $details = [
            'name' => $nameProduct->getName(),
            'sku' => $skuProduct->getSku(),
            'image' => $this->getProductImageUrl($imageProduct),
        ];

        return $details;
    }

    protected function getProductImageUrl(Product $product)
    {
        if (count($product->getImages()) == 0) {
            return static::DEFAULT_IMAGE_URL;
        }
        $product->getImages()->rewind();
        $image = $product->getImages()->current();
        return $image->getUrl();
    }

    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }
}
