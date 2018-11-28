<?php
namespace Products\Listing\Channel;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Name;
use CG\Ebay\Listing\Creator as EbayListingCreator;
use CG\FeatureFlags\Service as FeatureFlagService;
use CG\Listing\Client\Service as ListingService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Products\Listing\Channel\Factory as CreateListingsFactory;
use Products\Listing\Exception as ListingException;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const CHANNELS_SUPPORTED = ['ebay', 'shopify', 'big-commerce', 'woo-commerce'];

    /** @var FeatureFlagService */
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

    public function getAllowedCreateListingsChannels(OrganisationUnit $ou): array
    {
        $allowedChannels = [];
        /** @var Account $account */
        foreach ($this->getAllowedChannelsForOu($ou) as $channel) {
            $allowedChannels[$channel] = $this->channelName->lookupChannel($channel, null, ucfirst($channel));
        }
        return $allowedChannels;
    }

    public function getAllowedCreateListingsVariationsChannels(OrganisationUnit $ou): array
    {
        $allowedChannels = $this->getAllowedCreateListingsChannels($ou);
        if (!$this->featureFlagService->isActive(ListingService::FEATURE_FLAG_CREATE_LISTINGS_VARIATIONS_AMAZON, $ou)) {
            unset($allowedChannels['amazon']);
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

    public function getAccountData(Account $account, array  $postData = []): array
    {
        try {
            /** @var AccountDataInterface $service */
            $service = $this->factory->fetchAndValidateChannelService($account, AccountDataInterface::class, $postData);
            return $service->getAccountData($account);
        } catch (ListingException $e) {
            return $account->toArray();
        } catch (\Throwable $e) {
            $this->logErrorException($e, 'There was an error while getting the account data for account %s', [$account->getId()], 'ChannelServiceGetAccountData');
            return $account->toArray();
        }
    }

    protected function getAllowedChannelsForOu(OrganisationUnit $ou): array
    {
        if (!$this->featureFlagService->isActive(ListingService::FEATURE_FLAG_CREATE_LISTINGS_AMAZON, $ou)) {
            return static::CHANNELS_SUPPORTED;
        }
        return array_merge(static::CHANNELS_SUPPORTED, ['amazon' => 'amazon']);
    }

    public function isProductSearchActive(OrganisationUnit $ou): bool
    {
        return $this->featureFlagService->isActive(EbayListingCreator::FEATURE_FLAG_PBSE, $ou);
    }

    public function isProductSearchActiveForVariations(OrganisationUnit $ou): bool
    {
        return $this->featureFlagService->isActive(EbayListingCreator::FEATURE_FLAG_PBSE_VARIATIONS, $ou);
    }

    public function formatExternalChannelData(string $channel, array $data, string $processGuid): array
    {
        try {
            /** @var ChannelDataInterface $service */
            $service = $this->factory->fetchAndValidateChannelService($channel, ChannelDataInterface::class, $data);
            return $service->formatExternalChannelData($data, $processGuid);
        } catch (ListingException $e) {
            return $data;
        }
    }
}
