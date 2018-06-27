<?php
namespace Products\Listing\Channel;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Name;
use CG\FeatureFlags\Service as FeatureFlagService;
use CG\Listing\Client\Service as ListingService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use Products\Listing\Channel\Factory as CreateListingsFactory;

class Service
{
    const CHANNELS_SUPPORTED = ['ebay', 'shopify', 'big-commerce', 'woo-commerce'];

    /** @var  FeatureFlagService */
    protected $featureFlagService;
    /** @var Name */
    protected $channelName;
    /** @var AccountService */
    protected $accountService;
    /** @var CreateListingsFactory */
    protected $factory;

    public function __construct(
        FeatureFlagService $featureFlagService,
        Name $channelName,
        AccountService $accountService,
        CreateListingsFactory $factory
    ) {
        $this->featureFlagService = $featureFlagService;
        $this->channelName = $channelName;
        $this->accountService = $accountService;
        $this->factory = $factory;
    }

    public function getAllowedCreateListingsChannels(): array {
        $allowedChannels = [];
        /** @var Account $account */
        foreach (static::CHANNELS_SUPPORTED as $channel) {
            $allowedChannels[$channel] = $this->channelName->lookupChannel($channel, null, ucfirst($channel));
        }
        return $allowedChannels;
    }

    public function getDefaultSettingsForAccount(Account $account, array $postData = []): array
    {
        /** @var DefaultAccountSettingsInterface $channelService */
        $channelService = $this->factory->fetchAndValidateChannelService($account, DefaultAccountSettingsInterface::class, $postData);
        return $channelService->getDefaultSettingsForAccount($account);
    }

    public function getChannelSpecificFieldValues(Account $account, array $postData = []): array
    {
        /** @var ChannelSpecificValuesInterface $service */
        $service = $this->factory->fetchAndValidateChannelService($account, ChannelSpecificValuesInterface::class, $postData);
        return $service->getChannelSpecificFieldValues($account);
    }

    public function getCategoryDependentValues(Account $account, int $categoryId, array $postData = []): array
    {
        /** @var CategoryDependentServiceInterface $service */
        $service = $this->factory->fetchAndValidateChannelService($account, CategoryDependentServiceInterface::class, $postData);
        return $service->getCategoryDependentValues($account, $categoryId);
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, int $categoryId, array $postData = []): array
    {
        /** @var CategoryChildrenInterface $service */
        $service = $this->factory->fetchAndValidateChannelService($account, CategoryChildrenInterface::class, $postData);
        return $service->getCategoryChildrenForCategoryAndAccount($account, $categoryId);
    }

    public function refetchAndSaveCategories(Account $account, array $postData = []): array
    {
        /** @var CategoriesRefreshInterface $service */
        $service = $this->factory->fetchAndValidateChannelService($account, CategoriesRefreshInterface::class, $postData);
        return $service->refetchAndSaveCategories($account);
    }

    public function refreshAndFetchAccountPolicies(Account $account, array $postData = []): array
    {
        /** @var AccountPoliciesInterface $service */
        $service = $this->factory->fetchAndValidateChannelService($account, AccountPoliciesInterface::class, $postData);
        return $service->refreshAccountPolicies($account);
    }
}
