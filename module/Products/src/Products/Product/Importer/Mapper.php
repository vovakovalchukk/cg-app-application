<?php
namespace Products\Product\Importer;

use CG\Product\Unimported\Entity as UnimportedProduct;
use Products\Product\Importer;

class Mapper
{
    public function importLineToUnimportedProduct(array $productLine): UnimportedProduct
    {
        return new UnimportedProduct(
            $productLine[Importer::HEADER_TITLE],
            $productLine[Importer::HEADER_SKU],
            $productLine[Importer::HEADER_QTY]
        );
    }
}