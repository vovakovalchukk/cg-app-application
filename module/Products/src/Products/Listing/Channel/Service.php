<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Name;
use CG\FeatureFlags\Service as FeatureFlagService;
use CG\Listing\Client\Service as ListingService;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Service
{
    const CHANNELS_SUPPORTED = ['ebay', 'shopify', 'big-commerce', 'woo-commerce'];

    /** @var  FeatureFlagService */
    protected $featureFlagService;
    /** @var Name */
    protected $channelName;

    public function __construct(FeatureFlagService $featureFlagService, Name $channelName)
    {
        $this->featureFlagService = $featureFlagService;
        $this->channelName = $channelName;
    }

    public function getAllowedCreateListingsChannels(
        OrganisationUnit $rootOu,
        $variationCreateListings = false
    ): array {
        $allowedChannels = [];

        if (
            $variationCreateListings
            && !$this->featureFlagService->isActive(ListingService::FEATURE_FLAG_CREATE_LISTINGS_VARIATIONS, $rootOu)
        ) {
            return $allowedChannels;
        }

        /** @var Account $account */
        foreach (static::CHANNELS_SUPPORTED as $channel) {
            $allowedChannels[$channel] = $this->channelName->lookupChannel($channel, null, ucfirst($channel));
        }
        return $allowedChannels;
    }
}
