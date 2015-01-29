<?php
namespace Orders\Order\PickList;

use CG\Product\Service\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\Image\Entity as Image;
use CG\Template\Element\Image as ImageElement;
use CG\PickList\Entity as PickList;

class Mapper
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->setProductService($productService);
    }

    public function fromItemsBySku(array $items, ProductCollection $products, $includeImages = true)
    {
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
                $title = $this->searchProductTitle($matchingProduct);
                $variation = $this->formatAttributes($matchingProduct->getAttributeValues());
                $image = ($includeImages === true) ? $this->getProductImage($matchingProduct) : null;
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

    public function sortEntries(array $pickListEntries, $field, $ascending = true)
    {
        usort($pickListEntries, function($a, $b) use ($field, $ascending) {
            $getter = 'get' . ucfirst(strtolower($field));
            $directionChanger = ($ascending === false) ? -1 : 1;

            if(is_string($a->$getter())) {
                return $directionChanger * strcasecmp($a->$getter(), $b->$getter());
            }
            return $directionChanger * ($a->$getter() - $b->$getter());
        });

        return $pickListEntries;
    }

    protected function getMostDescriptiveItemDetails(array $matchingItems)
    {
        $bestTitle= $matchingItems[0]->getItemName();
        $bestVariationAttributes = $matchingItems[0]->getItemVariationAttribute();

        foreach($matchingItems as $item) {
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

    protected function sumQuantities(array $items)
    {
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->getItemQuantity();
        }
        return $sum;
    }

    protected function searchProductTitle(Product $product)
    {
        if($product->getParentProductId() !== 0 &&
            ($product->getName() === '' || $product->getName() === null)
        ) {
            $parentProduct = $this->getProductService()->fetch($product->getParentProductId());
            return $parentProduct->getName();
        }

        return $product->getName();
    }

    protected function getProductImage(Product $product)
    {
        if($product->getImages() === null || $product->getImages()->count() === 0) {
            return null;
        }
        $product->getImages()->rewind();
        return $this->convertImageToTemplateElement($product->getImages()->current());
    }

    protected function convertImageToTemplateElement(Image $image)
    {
        return new ImageElement(
            base64_encode(file_get_contents($image->getUrl())),
            strtolower(array_pop(explode('.', $image->getUrl())))
        );
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->productService;
    }

    /**
     * @param ProductService $productService
     * @return $this
     */
    public function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }
}