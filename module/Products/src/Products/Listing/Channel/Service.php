<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\FeatureFlags\Service as FeatureFlagService;
use CG\Listing\Client\Service as ListingService;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Service
{
    const CHANNEL_FEATURE_FLAG_MAP = [
        'ebay' => ListingService::FEATURE_FLAG_CREATE_LISTINGS,
        'shopify' => ListingService::FEATURE_FLAG_CREATE_LISTINGS_SHOPIFY,
        'big-commerce' => ListingService::FEATURE_FLAG_CREATE_LISTINGS_BIGCOMMERCE,
        'woo-commerce' => ListingService::FEATURE_FLAG_CREATE_LISTINGS_WOOCOMMERCE,
    ];

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
        /** @var Account $account */
        foreach ($accounts as $account) {
            if (!isset(static::CHANNEL_FEATURE_FLAG_MAP[$account->getChannel()])) {
                continue;
            }
            if (isset($allowedChannels[$account->getChannel()])) {
                continue;
            }
            $featureFlag = static::CHANNEL_FEATURE_FLAG_MAP[$account->getChannel()];
            if (!$this->featureFlagService->isActive($featureFlag, $ou)) {
                continue;
            }
            $allowedChannels[$account->getChannel()] = $account->getDisplayChannel() ?? ucfirst($account->getChannel());
        }
        return $allowedChannels;
    }
}
