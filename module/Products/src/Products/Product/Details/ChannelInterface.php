<?php
namespace Products\Product\Details;

interface ChannelInterface
{
    public function appendDetails(int $productId, array &$productDetails): void;
    public function saveDetails(int $productId, array $details): void;
}