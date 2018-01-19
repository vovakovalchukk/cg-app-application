<?php
namespace Products\Listing\Create\Ebay;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Ebay\Category\ExternalData\Data;
use CG\Ebay\Credentials;
use CG\Order\Client\Shipping\Method\Storage\Api as ShippingMethodService;
use CG\Order\Shared\Shipping\Method\Collection as ShippingMethodCollection;
use CG\Order\Shared\Shipping\Method\Entity as ShippingMethod;
use CG\Order\Shared\Shipping\Method\Filter as ShippingMethodFilter;
use CG\Product\Category\Collection as CategoryCollection;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Ebay\Category\ExternalData\FeatureHelper;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
use CG\Product\Category\ExternalData\Entity as CategoryExternal;

class Service
{
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

    public function getCategoryOptionsForAccount(Account $account): array
    {
        return $this->formatCategoriesArray(
            $this->fetchCategoriesForAccount($account)
        );
    }

    public function getShippingMethodsForAccount(Account $account): array
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

    public function getCurrencySymbolForAccount(Account $account): ?string
    {
        try {
            $siteId = $this->getEbaySiteIdForAccount($account);
            return CurrencyMap::getCurrencySymbolBySiteId($siteId);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function getCategoryChildrenForCategory(int $externalCategoryId): array
    {
        try {
            $category = $this->fetchCategoryByExternalId($externalCategoryId);
            $childCategories = $this->categoryService->fetchCollectionByFilter(
                (new CategoryFilter('all', 1))
                    ->setParentId([$category->getId()])
            );
            return $this->formatCategoriesArray($childCategories);
        } catch (NotFound $e) {
            return [];
        }
    }

    public function getListingDurationsForCategory(int $externalCategoryId): array
    {
        try {
            $category = $this->fetchCategoryByExternalId($externalCategoryId);
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

    protected function fetchCategoryByExternalId(int $externalId): Category
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryService->fetchCollectionByFilter(
            (new CategoryFilter(1, 1))
                ->setExternalId([$externalId])
                ->setChannel(['ebay'])
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
