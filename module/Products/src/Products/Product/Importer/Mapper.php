<?php
namespace Products\Product\Importer;

use CG\Product\Unimported\Entity as UnimportedProduct;
use Products\Product\Importer;

class Mapper
{
    public function importLineToUnimportedProduct(array $productLine, ?array $variations = []): UnimportedProduct
    {
        return new UnimportedProduct(
            $productLine[Importer::HEADER_TITLE],
            $productLine[Importer::HEADER_SKU],
            $productLine[Importer::HEADER_QTY],
            $variations,
            $this->importLineToVariationAttributes($productLine)
        );
    }

    protected function importLineToVariationAttributes(array $variationLine): array
    {
        $variationAttributes = [];
        if (!$this->isVariationLine($variationLine)) {
            return $variationAttributes;
        }
        foreach ($variationLine as $header => $value) {
            $matches = [];
            if ($header != Importer::HEADER_VARIATION_SET && !preg_match(Importer::HEADER_VARIATION_ATTR_REGEX, $header, $matches)) {
                continue;
            }
            $attributeName = trim($matches[1]);
            $variationAttributes[$attributeName] = $value;
        }
        return $variationAttributes;
    }

    protected function isVariationLine(array $productLine): bool
    {
        return (isset($productLine[Importer::HEADER_VARIATION_SET]) && trim($productLine[Importer::HEADER_VARIATION_SET]) != '');
    }
}