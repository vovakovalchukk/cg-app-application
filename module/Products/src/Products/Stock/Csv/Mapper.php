<?php
namespace Products\Stock\Csv;

use CG\Product\Collection as Products;
use CG\Stock\Collection as Stocks;
use CG\Stock\Entity as Stock;

class Mapper
{
    public function stockCollectionToCsvArray(Stocks $stocks, Products $products = null)
    {
        $csvData = [];

        /** @var Stock $stock */
        foreach ($stocks as $stock) {
            $productName = $products ? $this->getProductName($stock, $products) : '';
            $csvData[] = [$stock->getSku(), $productName, $stock->getTotalOnHand()];
        }

        return $csvData;
    }

    protected function getProductName(Stock $stock, Products $products)
    {
        $product = $products->getBy('sku', $stock->getSku());
        if ($product->count() == 0) {
            return '';
        }

        $product->rewind();
        if ($product->current()->isVariation()) {
            return $products->getById($product->current()->getParentProductId())->getName()
                . ' (' . implode('/', $product->current()->getAttributeValues()) . ')';
        }
        return $product->current()->getName();
    }
}
