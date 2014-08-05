<?php
namespace Products\Product\BulkActions;

use CG\Product\Entity;

interface ProductAwareInterface
{
    public function setProduct(Entity $product);
} 