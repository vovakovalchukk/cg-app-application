<?php
namespace Products\Listing\Channel;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\FeatureFlags\Service as FeatureFlagService;
use CG\Listing\Client\Service as ListingService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use Products\Listing\Channel\Factory as CreateListingsFactory;

class Service
{
    const CHANNELS_SUPPORTED = ['ebay', 'shopify', 'big-commerce', 'woo-commerce'];

    /** @var  FeatureFlagService */
    protected $featureFlagService;
    /** @var AccountService */
    protected $accountService;
    /** @var CreateListingsFactory */
    protected $factory;

    public function __construct(
        FeatureFlagService $featureFlagService,
        AccountService $accountService,
        CreateListingsFactory $factory
    ) {
        $this->featureFlagService = $featureFlagService;
        $this->accountService = $accountService;
        $this->factory = $factory;
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

    public function getDefaultSettingsForAccount(Account $account, array $postData = [])
    {
        /** @var DefaultAccountSettingsInterface $channelService */
        $channelService = $this->factory->fetchAndValidateChannelService($account, DefaultAccountSettingsInterface::class, $postData);
        return $channelService->getDefaultSettingsForAccount($account);
    }

    public function getChannelSpecificFieldValues(Account $account, array $postData = [])
    {
        /** @var ChannelSpecificValuesInterface $service */
        $service = $this->factory->fetchAndValidateChannelService($account, ChannelSpecificValuesInterface::class, $postData);
        return $service->getChannelSpecificFieldValues($account);
    }

    public function getCategoryDependentValues(Account $account, int $categoryId, array $postData = [])
    {
        /** @var CategoryDependentServiceInterface $service */
        $service = $this->factory->fetchAndValidateChannelService($account, CategoryDependentServiceInterface::class, $postData);
        return $service->getCategoryDependentValues($account, $categoryId);
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, int $categoryId, array $postData = [])
    {
        /** @var CategoryChildrenInterface $service */
        $service = $this->factory->fetchAndValidateChannelService($account, CategoryChildrenInterface::class, $postData);
        return $service->getCategoryChildrenForCategoryAndAccount($account, $categoryId);
    }

    public function refetchAndSaveCategories(Account $account, array $postData = [])
    {
        /** @var CategoriesRefreshInterface $service */
        $service = $this->factory->fetchAndValidateChannelService($account, CategoriesRefreshInterface::class, $postData);
        return $service->refetchAndSaveCategories($account);
    }
}
