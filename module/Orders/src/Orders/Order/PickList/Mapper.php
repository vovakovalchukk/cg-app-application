<?php
namespace Orders\Order\PickList;

use CG\Order\Shared\Item\Entity as Item;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\Image\Entity as Image;
use CG\Stdlib\Exception\Runtime\InvalidKey;
use CG\Template\Element\Image as ImageElement;
use CG\PickList\Entity as PickList;

class Mapper
{
    protected $imageClient;

    public function __construct(ImageClient $imageClient)
    {
        $this->imageClient = $imageClient;
    }

    public function fromItemsAndProductsBySku(
        array $items,
        ProductCollection $products,
        ProductCollection $parentProducts,
        array $images = []
    ) {
        $pickListEntries = [];

        foreach($items as $sku => $matchingItems) {
            $productCollection = $products->getBy('sku', $sku);
            $productCollection->rewind();
            $matchingProduct = $productCollection->current();
            $image = null;

            /** @var Product $matchingProduct */
            if($matchingProduct === null) {
                $description = $this->getMostDescriptiveItemDetails($matchingItems);
                $title = $description['title'];
                $variation = $this->formatAttributes($description['variationAttributes']);
            } else {
                $title = $this->searchProductTitle($matchingProduct, $parentProducts);
                $variation = $this->formatAttributes($matchingProduct->getAttributeValues());
                $image = (isset($images[$sku])) ? $this->convertImageToTemplateElement($images[$sku])  : null;
            }

            $pickListEntries[] = new PickList(
                $title,
                $this->sumQuantities($matchingItems),
                $sku,
                $variation,
                $image
            );
        }

        return $pickListEntries;
    }

    public function fromItemsByTitle(array $items)
    {
        $pickListEntries = [];

        foreach($items as $title => $matchingItems) {
            $pickListEntries[] = new PickList(
                $title,
                $this->sumQuantities($matchingItems),
                '',
                $this->formatAttributes($this->getMostDescriptiveItemDetails($matchingItems)['variationAttributes']),
                null
            );
        }

        return $pickListEntries;
    }

    /**
     * @param Item[] $items
     * @return array
     */
    protected function getMostDescriptiveItemDetails(array $items)
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

    protected function searchProductTitle(Product $product, ProductCollection $parentProducts)
    {
        if($product->getParentProductId() !== 0 &&
            ($product->getName() === '' || $product->getName() === null)
        ) {
            $parentProduct = $parentProducts->getById($product->getParentProductId());
            return $parentProduct->getName();
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
}