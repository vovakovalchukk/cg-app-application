<?php
namespace Products\Listing\Create\Ebay;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Ebay\Credentials;
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

    public function __construct(CategoryService $categoryService, Cryptor $cryptor)
    {
        $this->categoryService = $categoryService;
        $this->cryptor = $cryptor;
    }

    public function getCategoryOptionsForAccount(Account $account): array
    {
        $categories = $this->fetchCategoriesForAccount($account);
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
}