<?php
namespace Products\Product\Details;

interface ChannelInterface
{
    public function fetchChannelDetails(array $productIds, array $accountIds = []): array;
    public function saveDetails(int $productId, array $details, int $accountId = null): void;
}