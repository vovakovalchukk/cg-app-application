<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\FeatureFlags\Service as FeatureFlagService;
use CG\Listing\Client\Service as ListingService;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Service
{
    const CHANNELS_SUPPORTED = ['ebay', 'shopify', 'big-commerce', 'woo-commerce'];

    /** @var  FeatureFlagService */
    protected $featureFlagService;

    public function __construct(FeatureFlagService $featureFlagService)
    {
        $this->featureFlagService = $featureFlagService;
    }

    /**
     * @param OrganisationUnit $ou
     * @param AccountCollection $accounts
     * @return array
     */
    public function getAllowedCreateListingsChannels(OrganisationUnit $ou, AccountCollection $accounts): array
    {
        $allowedChannels = [];
        if (!$this->featureFlagService->isActive(ListingService::FEATURE_FLAG_CREATE_LISTINGS, $ou)) {
            return $allowedChannels;
        }
        /** @var Account $account */
        foreach ($accounts as $account) {
            if (isset($allowedChannels[$account->getChannel()]) ||
                !in_array($account->getChannel(), static::CHANNELS_SUPPORTED)
            ) {
                continue;
            }
            $allowedChannels[$account->getChannel()] = $account->getDisplayChannel() ?? ucfirst($account->getChannel());
        }
        return $allowedChannels;
    }
}
