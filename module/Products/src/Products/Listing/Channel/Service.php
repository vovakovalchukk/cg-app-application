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
    private $channelFeatureFlagMap = [
        'ebay' => ListingService::FEATURE_FLAG_CREATE_LISTINGS,
        'shopify' => ListingService::FEATURE_FLAG_CREATE_LISTINGS_SHOPIFY,
        'big-commerce' => ListingService::FEATURE_FLAG_CREATE_LISTINGS_BIGCOMMERCE
    ];

    private $variationListingsFeatureFlagMap = [
        'ebay' => ListingService::FEATURE_FLAG_CREATE_LISTINGS_VARIATIONS_EBAY,
        'shopify' => ListingService::FEATURE_FLAG_CREATE_LISTINGS_VARIATIONS_SHOPIFY,
        'big-commerce' => ListingService::FEATURE_FLAG_CREATE_LISTINGS_VARIATIONS_BIGCOMMERCE,
        'woo-commerce' => ListingService::FEATURE_FLAG_CREATE_LISTINGS_VARIATIONS_WOOCOMMERCE
    ];

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
        $featureFlagMap = $variationCreateListings ? $this->variationListingsFeatureFlagMap : $this->channelFeatureFlagMap;
        /** @var Account $account */
        foreach ($featureFlagMap as $channel => $featureFlag) {
            if (!$this->featureFlagService->isActive($featureFlag, $rootOu)) {
                continue;
            }

            $allowedChannels[$channel] = $this->channelName->lookupChannel($channel, null, ucfirst($channel));
        }
        return $allowedChannels;
    }
}
