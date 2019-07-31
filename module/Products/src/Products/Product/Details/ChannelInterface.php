<?php
namespace Products\Product\Details;

interface ChannelInterface
{
    public function appendDetails(int $productId, array &$productDetails, array $accountIds = []): void;
    public function saveDetails(int $productId, array $details, int $accountId = null): void;
}