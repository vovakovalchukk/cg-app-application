<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as Items;
use CG\Order\Shared\Item\Entity as Item;
use CG\Product\Collection as Products;
use CG\Product\Entity as Product;
use CG\Stdlib\Exception\Runtime\NotFound;
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
            $callback = $this->getCallbackValueForField($item, $fieldName);
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
        if ($this->products === null) {
            return true;
        }
        if ($this->products->count() === 0) {
            return true;
        }
        return false;
    }

    protected function getCallbackValueForField(
        Item $item,
        array $field
    ): callable {
        return function() use($item, $field) {
            try {
                if ($value = $this->getValueFromProduct($field, $this->getProductForItem($item))) {
                    return $value;
                }
            } catch (NotFound $e) {
                //no-op
            }
            return $this->returnDefaultValueForField($field);
        };
    }

    protected function getProductForItem(Item $item): Product
    {
        $products = $this->products->getBy('sku', [$item->getItemSku()]);
        if ($product = $products->getFirst()) {
            return $product;
        }
        throw new NotFound();
    }

    protected function getValueFromProduct(array $field, Product $product)
    {
        $getter = 'get' . ucfirst($field['name']);
        $productWithEmbeds = $this->getProductWithEmbeds($product);
        if (isset($productWithEmbeds[$field['type']]) && is_callable([$productWithEmbeds[$field['type']], $getter])) {
            try {
                $value = $productWithEmbeds[$field['type']]->$getter();
                if (!empty($value)) {
                    return $value;
                }
            } catch (\BadMethodCallException $e) {
                //no-op
            }
        }
    }

    protected function getProductWithEmbeds(Product $product): array
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
