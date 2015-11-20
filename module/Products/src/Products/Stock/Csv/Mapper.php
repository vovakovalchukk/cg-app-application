<?php
namespace Products\Stock\Csv;

use CG\Stock\Collection as Stocks;

class Mapper
{
    public function stockCollectionToCsvArray(Stocks $stocks)
    {
        $csvData = [];

        foreach ($stocks as $stock) {
            $csvData[] = [$stock->getSku(), '', $stock->getTotalOnHand()];
        }

        return $csvData;
    }
}
