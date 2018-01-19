<?php
namespace Products\Listing\Create\Ebay;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
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

class Service
{
    /** @var CategoryService */
    protected $categoryService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var  ShippingMethodService */
    protected $shippingMethodService;

    public function __construct(
        CategoryService $categoryService,
        Cryptor $cryptor,
        ShippingMethodService $shippingMethodService
    ) {
        $this->categoryService = $categoryService;
        $this->cryptor = $cryptor;
        $this->shippingMethodService = $shippingMethodService;
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
            /** @var CategoryCollection $categoryCollection */
            $categoryCollection = $this->categoryService->fetchCollectionByFilter(
                (new CategoryFilter(1, 1))
                    ->setExternalId([$externalCategoryId])
            );
            $childCategories = $this->categoryService->fetchCollectionByFilter(
                (new CategoryFilter('all', 1))
                    ->setParentId([$categoryCollection->getFirst()->getId()])
            );
            return $this->formatCategoriesArray($childCategories);
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
}
