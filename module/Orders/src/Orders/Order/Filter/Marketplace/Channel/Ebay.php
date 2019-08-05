<?php
namespace Orders\Order\Filter\Marketplace\Channel;

use CG\Account\Shared\Entity as Account;
use CG\Ebay\Site\Map as SiteMap;
use Orders\Order\Filter\Marketplace\ChannelInterface;

class Ebay implements ChannelInterface
{
    public function __invoke(Account $account): array
    {
        // All eBay Accounts have access to all marketplaces
        $sites = SiteMap::getIdToNameMap();
        $options = [];
        foreach ($sites as $siteName) {
            $options[$siteName] = 'eBay ' . $siteName;
        }
        return $options;
    }
}