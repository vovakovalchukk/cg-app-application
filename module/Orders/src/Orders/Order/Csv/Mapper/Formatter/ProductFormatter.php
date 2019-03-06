<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as Items;
use CG\Order\Shared\Item\Entity as Item;
use CG\Product\Collection as Products;
use CG\Product\Entity as Product;
use Orders\Order\Csv\Mapper\ProductFormatterInterface;

class ProductFormatter implements ProductFormatterInterface
{
    const DEFAULT_EMPTY_VALUE = '';

    protected $products;

    public function __invoke(Order $order, $fieldName)
    {
        $columns = [];
        if ($order->getItems()->count() === 0) {
            $columns[] = $this->returnDefaultValueForField($fieldName);
        }

        if ($this->noProductsFound()) {
            return $this->returnDefaultValueForFieldForAllItems($fieldName, $order->getItems());
        }

        foreach ($order->getItems() as $item) {
            $relevantProducts = $this->getRelevantProductsForItem($item);
            $callback = $this->getCallbackValueForField($fieldName, $relevantProducts);
            $columns[] = $callback();
        }
        return $columns;
    }

    public function getProducts(): ?Products
    {
        return $this->products;
    }

    public function setProducts(?Products $products)
    {
        $this->products = $products;
        return $this;
    }

    protected function noProductsFound(): bool
    {
        $products = $this->getProducts();
        if ($products === null) {
            return true;
        }
        if ($products->count() === 0) {
            return true;
        }
        return false;
    }

    protected function getCallbackValueForField(
        array $field,
        Products $products
    ): callable {
        return function() use($field, $products) {
            /** @var Product $product */
            foreach ($products as $product) {
                if ($value = $this->getValueFromProduct($field, $product)) {
                    return $value;
                }
            }
            return $this->returnDefaultValueForField($field);
        };
    }

    protected function getValueFromProduct(array $field, Product $product)
    {
        $getter = 'get' . ucfirst($field['name']);
        $objects = $this->getObjectsForProduct($product);
        if (isset($objects[$field['type']]) && is_callable([$objects[$field['type']], $getter])) {
            try {
                $value = $objects[$field['type']]->$getter();
                if (!empty($value)) {
                    return $value;
                }
            } catch (\BadMethodCallException $e) {
                //no-op
            }
        }
    }

    protected function getRelevantProductsForItem(Item $item): Products
    {
        $itemSku = $item->getItemSku();
        $relevantProducts = new Products(Product::class, __METHOD__);
        $relevantProducts->addAll($this->getProducts()->getBy('sku', [$itemSku]));
        $parentProductIds = $relevantProducts->getArrayOf('ParentProductId');
        $relevantProducts->addAll($this->getProducts()->getBy('parentProductId', $parentProductIds));
        return $relevantProducts;
    }

    protected function getObjectsForProduct(Product $product): array
    {
        $objects = ['product' => $product];
        if ($product->hasDetails()) {
            $objects += ['detail' => $product->getDetails()];
        }
        return $objects;
    }

    protected function returnDefaultValueForField(array $field): string
    {
        return isset($field['default']) ? $field['default'] : static::DEFAULT_EMPTY_VALUE;
    }

    protected function returnDefaultValueForFieldForAllItems(array $field, Items $items): array
    {
        return array_pad(
            [],
            $items->count(),
            $this->returnDefaultValueForField($field)
        );
    }
}
