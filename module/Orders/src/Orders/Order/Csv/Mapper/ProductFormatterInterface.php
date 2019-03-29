<?php
namespace Orders\Order\Csv\Mapper;

use CG\Product\Collection as Products;

interface ProductFormatterInterface extends FormatterInterface
{
    public function getProducts(): ?Products;
    public function setProducts(?Products $products);
}