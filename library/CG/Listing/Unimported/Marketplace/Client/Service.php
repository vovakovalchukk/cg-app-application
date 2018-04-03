<?php
namespace CG\Listing\Unimported\Marketplace\Client;

use CG\Account\Shared\Entity as Account;
use CG\Listing\Unimported\Marketplace\Service as BaseService;

class Service extends BaseService
{
    public function getMarketplaceIdToNameMap(Account $account): array
    {
        $marketplaceIdToNameMap = $account->getExternalData()['marketplace'] ?? null;
        if (is_string($marketplaceIdToNameMap)) {
            $marketplaceIdToNameMap = @unserialize($marketplaceIdToNameMap);
        }
        return is_array($marketplaceIdToNameMap) ? $marketplaceIdToNameMap : [];
    }

    public function mapMarketplaceIdToName(Account $account, string $marketplaceId): string
    {
        return $this->getMarketplaceIdToNameMap($account)[$marketplaceId] ?? $marketplaceId;
    }
}