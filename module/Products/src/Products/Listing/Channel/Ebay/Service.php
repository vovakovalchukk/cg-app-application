<?php
namespace Products\Listing\Channel\Ebay;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Ebay\Category\ExternalData\Data;
use CG\Ebay\Category\ExternalData\FeatureHelper;
use CG\Ebay\Credentials;
use CG\Order\Client\Shipping\Method\Storage\Api as ShippingMethodService;
use CG\Order\Shared\Shipping\Method\Collection as ShippingMethodCollection;
use CG\Order\Shared\Shipping\Method\Entity as ShippingMethod;
use CG\Order\Shared\Shipping\Method\Filter as ShippingMethodFilter;
use CG\Product\Category\Collection as CategoryCollection;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\ExternalData\Entity as CategoryExternal;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\CategoryDependentServiceInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;
use Products\Listing\Channel\DefaultAccountSettingsInterface;

class Service implements
    CategoryDependentServiceInterface,
    DefaultAccountSettingsInterface,
    ChannelSpecificValuesInterface,
    CategoryChildrenInterface
{
    const ALLOWED_SETTINGS_KEYS = [
        'listingLocation' => 'listingLocation',
        'listingCurrency' => 'listingCurrency',
        'paypalEmail' => 'paypalEmail',
        'listingDuration' => 'listingDuration',
        'listingDispatchTime' => 'listingDispatchTime',
        'listingPaymentMethods' => 'listingPaymentMethods'
    ];

    /** @var CategoryService */
    protected $categoryService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var ShippingMethodService */
    protected $shippingMethodService;
    /** @var CategoryExternalService */
    protected $categoryExternalService;

    public function __construct(
        CategoryService $categoryService,
        Cryptor $cryptor,
        ShippingMethodService $shippingMethodService,
        CategoryExternalService $categoryExternalService
    ) {
        $this->categoryService = $categoryService;
        $this->cryptor = $cryptor;
        $this->shippingMethodService = $shippingMethodService;
        $this->categoryExternalService = $categoryExternalService;
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, string $externalCategoryId): array
    {
        try {
            $category = $this->fetchCategoryByExternalIdAndMarketplace(
                $this->getEbaySiteIdForAccount($account),
                $externalCategoryId
            );
            $childCategories = $this->categoryService->fetchCollectionByFilter(
                (new CategoryFilter('all', 1))
                    ->setParentId([$category->getId()])
            );
            return $this->formatCategoriesArray($childCategories);
        } catch (NotFound $e) {
            return [];
        }
    }

    public function getCategoryDependentValues(Account $account, string $externalCategoryId): array
    {
        return [
            'listingDuration' => $this->getListingDurationsForCategory($account, $externalCategoryId)
        ];
    }

    public function getDefaultSettingsForAccount(Account $account): array
    {
        return $this->filterDefaultSettingsKeys($account->getExternalData());
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'category' => $this->getCategoryOptionsForAccount($account),
            'shippingService' => $this->getShippingMethodsForAccount($account),
            'currency' => $this->getCurrencySymbolForAccount($account)
        ];
    }

    protected function getListingDurationsForCategory(Account $account,int $externalCategoryId): array
    {
        try {
            $category = $this->fetchCategoryByExternalIdAndMarketplace(
                $this->getEbaySiteIdForAccount($account),
                $externalCategoryId
            );
            /** @var CategoryExternal $categoryExternal */
            $categoryExternal = $this->categoryExternalService->fetch($category->getId());
            /** @var Data $ebayData */
            $ebayData = $categoryExternal->getData();
            $listingDurations = (new FeatureHelper($ebayData))->getListingDurationsForType();
            return $this->formatListingDurationsArray($listingDurations);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function filterDefaultSettingsKeys(array $data)
    {
        return array_filter(
            $data,
            function($key) {
                return isset(static::ALLOWED_SETTINGS_KEYS[$key]);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function getCategoryOptionsForAccount(Account $account): array
    {
        return $this->formatCategoriesArray(
            $this->fetchCategoriesForAccount($account)
        );
    }

    protected function getShippingMethodsForAccount(Account $account): array
    {
        try {
            /** @var ShippingMethodCollection $shippingMethods */
            $shippingMethods = $this->shippingMethodService->fetchCollectionByFilter(
                new ShippingMethodFilter('all', 1, [], ['ebay'], [], [$account->getRootOrganisationUnitId()])
            );
            return $this->formatShippingMethodsArray($shippingMethods);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function getCurrencySymbolForAccount(Account $account): ?string
    {
        try {
            $siteId = $this->getEbaySiteIdForAccount($account);
            return CurrencyMap::getCurrencySymbolBySiteId($siteId);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    protected function formatCategoriesArray(CategoryCollection $categories): array
    {
        $categoryOptions = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $categoryOptions[$category->getExternalId()] = $category->getTitle();
        }
        return $categoryOptions;
    }

    protected function fetchCategoriesForAccount(Account $account): CategoryCollection
    {
        try {
            return $this->categoryService->fetchCollectionByFilter($this->buildCategoryFilterForAccount($account));
        } catch (NotFound $e) {
            return new CategoryCollection(Category::class, 'empty');
        }
    }

    protected function buildCategoryFilterForAccount(Account $account): CategoryFilter
    {
        $siteId = $this->getEbaySiteIdForAccount($account);
        return (new CategoryFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setChannel(['ebay'])
            ->setParentId([0])
            ->setMarketplace([$siteId])
            ->setListable(true)
            ->setEnabled(true);
    }

    protected function getEbaySiteIdForAccount(Account $account): int
    {
        /** @var Credentials $credentials */
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        return $credentials->getSiteId();
    }

    protected function formatShippingMethodsArray(ShippingMethodCollection $shippingMethods): array
    {
        $methods = [];
        /** @var ShippingMethod $shippingMethod */
        foreach ($shippingMethods as $shippingMethod) {
            $methods[$shippingMethod->getMethod()] = $shippingMethod->getMethod();
        }
        return $methods;
    }

    protected function fetchCategoryByExternalIdAndMarketplace(string $marketplace, int $externalId): Category
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryService->fetchCollectionByFilter(
            (new CategoryFilter(1, 1))
                ->setExternalId([$externalId])
                ->setChannel(['ebay'])
                ->setMarketplace([$marketplace])
        );
        return $categoryCollection->getFirst();
    }

    /**
     * A listing duration from the array looks like Days_30 or Days_7, so we can split it out by the underscore
     * to format a human readable display name
     * Also, it can have a value like GTC, so, if this is the case, just return it as it is.
     * @param array $listingDurations
     * @return array
     */
    protected function formatListingDurationsArray(array $listingDurations): array
    {
        $durations = [];
        foreach ($listingDurations as $listingDuration) {
            $durationName = $listingDuration;
            $durationArray = explode('_', $listingDuration);
            if (count($durationArray) === 2) {
                $durationName = array_pop($durationArray) . ' ' . array_pop($durationArray);
            }
            $durations[$listingDuration] = $durationName;
        }
        return $durations;
    }
}
