<?php
namespace Orders\Order\PickList;

use CG\Order\Shared\Item\Entity as Item;
use CG\PickList\Entity as PickList;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\Stdlib\Exception\Runtime\InvalidKey;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Template\Element\Image as ImageElement;
use CG\Template\Image\Map as ImageMap;

class Mapper
{
    public function fromItemsAndProductsBySku(
        array $items,
        ProductCollection $products,
        ProductCollection $parentProducts,
        ImageMap $imageMap = null
    ) {
        $pickListEntries = [];

        foreach ($items as $sku => $matchingItems) {
            $productCollection = $products->getBy('sku', $sku);
            $productCollection->rewind();
            $matchingProduct = $productCollection->current();

            /** @var Product $matchingProduct */
            if ($matchingProduct === null) {
                $description = $this->searchMostDescriptiveItemDetails($matchingItems);
                $title = $description['title'];
                $variation = $this->formatAttributes($description['variationAttributes']);
            } else {
                $title = $this->searchProductTitleInCollection($productCollection, $parentProducts);
                $variation = $this->formatAttributes($matchingProduct->getAttributeValues());
            }

            $pickListEntries[] = new PickList(
                $title,
                $this->sumQuantities($matchingItems),
                $sku,
                $variation,
                $this->getSkuImage($sku, $imageMap)
            );
        }
        return $pickListEntries;
    }

    protected function getSkuImage($sku, ImageMap $imageMap = null)
    {
        if ($imageMap != null && $imageMap->contentExists($sku)) {
            return $this->convertImageToTemplateElement($imageMap->getContentsForSku($sku));
        }
        return null;
    }

    public function fromItemsByTitle(array $items)
    {
        $pickListEntries = [];

        foreach($items as $title => $matchingItems) {
            $pickListEntries[] = new PickList(
                $title,
                $this->sumQuantities($matchingItems),
                '',
                $this->formatAttributes($this->searchMostDescriptiveItemDetails($matchingItems)['variationAttributes']),
                null
            );
        }

        return $pickListEntries;
    }

    /**
     * @param Item[] $items
     * @return array
     */
    protected function searchMostDescriptiveItemDetails(array $items)
    {
        $bestTitle= $items[0]->getItemName();
        $bestVariationAttributes = $items[0]->getItemVariationAttribute();

        foreach($items as $item) {
            $bestTitle = (strlen($item->getItemName()) > strlen($bestTitle)) ? $item->getItemName() : $bestTitle;

            $bestVariationAttributes =
                (count($item->getItemVariationAttribute()) > count($bestVariationAttributes)) ?
                    $item->getItemVariationAttribute() : $bestVariationAttributes;
        }

        return [
            'title' => $bestTitle,
            'variationAttributes' => $bestVariationAttributes
        ];
    }

    protected function formatAttributes(array $attributes)
    {
        $mergedKeyVals = [];
        foreach($attributes as $attribute => $value) {
            $mergedKeyVals[] = $attribute . ': ' . $value;
        }
        return implode("\n", $mergedKeyVals);
    }

    /**
     * @param Item[] $items
     * @return int
     */
    protected function sumQuantities(array $items)
    {
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->getItemQuantity();
        }
        return $sum;
    }

    protected function searchProductTitleInCollection(ProductCollection $productCollection, ProductCollection $parentProducts)
    {
        $title = '';
        foreach ($productCollection as $matchingProduct) {
            try {
                $title = $this->searchProductTitle($matchingProduct, $parentProducts);
                break;
            } catch (NotFound $e) {
                // no-op
            }
        }
        return $title;
    }

    protected function searchProductTitle(Product $product, ProductCollection $parentProducts)
    {
        $title = [];
        if ($product->getParentProductId() !== 0) {
            $parentProduct = $parentProducts->getById($product->getParentProductId());
            if (is_null($parentProduct)) {
                throw new NotFound(sprintf('Parent product with id %s not found', [$product->getParentProductId()]));
            }

            $title[] = $parentProduct->getName();
            if ($product->getName() != '') {
                $title[] = '('.$product->getName().')';
            }

            return implode("\n", $title);
        }

        return $product->getName();
    }

    protected function convertImageToTemplateElement($imageContents)
    {
        $encodedContents = base64_encode($imageContents);

        try {
            return new ImageElement(
                $encodedContents,
                $this->getImageType($encodedContents)
            );
        } catch(InvalidKey $e) {
            return null;
        }
    }

    protected function getImageType($encodedImage)
    {
        $uri = 'data://application/octet-stream;base64,' . $encodedImage;
        return image_type_to_extension(exif_imagetype($uri), false);
    }

    protected function getProductName(Product $product, Product $parentProduct): string
    {
        $name = [];

        $name[] = $parentProduct->getName();
        if ($product->getName() != '') {
            $name[] = '('.$product->getName().')';
        }

        return implode("\n", $name);
    }
}
