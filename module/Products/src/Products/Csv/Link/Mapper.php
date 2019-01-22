<?php
namespace Products\Csv\Link;

use CG\Location\Service as LocationService;
use CG\Location\Type as LocationType;
use CG\Product\Collection as Products;
use CG\Stock\Collection as Stocks;
use CG\Stock\Entity as Stock;
use CG\Stock\Location\Collection as StockLocations;

class Mapper
{
    protected $locationService;
    protected $merchantLocationIds = [];

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function getMerchantLocationIds(int $ouId)
    {
        if (!isset($this->merchantLocationIds[$ouId])) {
            $this->merchantLocationIds[$ouId] = array_values($this->locationService->fetchIdsByType(
                [LocationType::MERCHANT],
                $ouId
            ));
        }
        return $this->merchantLocationIds[$ouId];
    }

    public function stockCollectionToCsvArray(Stocks $stocks, Products $products = null, array $locationIds = null)
    {
        /** @var Stock $stock */
        foreach ($stocks as $stock) {
            $locationIds = $locationIds ?? $this->getMerchantLocationIds($stock->getOrganisationUnitId());
            if (empty($locationIds)) {
                continue;
            }

            /** @var StockLocations $stockLocations */
            $stockLocations = $stock->getLocations($locationIds);
            if ($stockLocations->count() == 0) {
                continue;
            }

            $productName = $products ? $this->getProductName($stock, $products) : '';
            yield [$stock->getSku(), $productName, $stockLocations->getTotalOnHand()];
        }
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
