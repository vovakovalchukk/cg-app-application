<?php
namespace Products\Listing\Channel\BigCommerce;

use CG\Account\Shared\Entity as Account;
use CG\BigCommerce\Category\Importer as CategoryImporter;
use CG\BigCommerce\Category\Service as BigCommerceCategoryService;
use CG\Product\Category\Collection as CategoryCollection;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;
use Products\Listing\Exception as ListingException;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface,
    CategoryChildrenInterface
{
    /** @var  CategoryService */
    protected $categoryService;
    /** @var BigCommerceCategoryService */
    protected $bigCommerceCategoryService;
    /** @var  CategoryImporter */
    protected $categoryImporter;

    public function __construct(
        CategoryService $categoryService,
        BigCommerceCategoryService $bigCommerceCategoryService,
        CategoryImporter $categoryImporter
    ) {
        $this->categoryService = $categoryService;
        $this->bigCommerceCategoryService = $bigCommerceCategoryService;
        $this->categoryImporter = $categoryImporter;
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return ['categories' => $this->fetchCategoriesForAccount($account)];
    }

    public function refetchAndSaveCategories(Account $account)
    {
        $this->saveCategories(
            $this->fetchCategoriesFromBigCommerce($account),
            $account
        );
        return [$this->fetchCategoriesForAccount($account)];
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, string $externalCategoryId)
    {
        try {
            $parentCategory = $this->fetchCategoryForExternalId($account, $externalCategoryId);
            /** @var CategoryCollection $categories */
            $categories = $this->categoryService->fetchCollectionByFilter(
                (new CategoryFilter())
                    ->setLimit('all')
                    ->setPage(1)
                    ->setAccountId([$account->getId()])
                    ->setParentId([$parentCategory->getId()])
                    ->setChannel(['big-commerce'])
                    ->setEnabled(true)
                    ->setListable(true)
            );
            return $this->formatCategoriesResponse($categories);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function fetchCategoryForExternalId(Account $account, string $externalCategoryId): Category
    {
        /** @var CategoryCollection $categories */
        $categories = $this->categoryService->fetchCollectionByFilter(
            (new CategoryFilter())
                ->setLimit(1)
                ->setPage(1)
                ->setAccountId([$account->getId()])
                ->setChannel(['big-commerce'])
                ->setExternalId([$externalCategoryId])
        );
        /** @var Category $category */
        $category = $categories->getFirst();
        return $category;
    }

    protected function fetchCategoriesForAccount(Account $account): array
    {
        try {
            /** @var CategoryCollection $categories */
            $categories = $this->categoryService->fetchCollectionByFilter(
                (new CategoryFilter())
                    ->setLimit('all')
                    ->setPage(1)
                    ->setAccountId([$account->getId()])
                    ->setParentId([0])
                    ->setChannel(['big-commerce'])
                    ->setEnabled(true)
                    ->setListable(true)
            );
            return $this->formatCategoriesResponse($categories);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function formatCategoriesResponse(CategoryCollection $categoryCollection): array
    {
        $categories = [];
        /** @var Category $category */
        foreach ($categoryCollection as $category) {
            $categories[$category->getExternalId()] = $category->getTitle();
        }
        return $categories;
    }

    protected function fetchCategoriesFromBigCommerce(Account $account): array
    {
        try {
            return $this->bigCommerceCategoryService->fetchCategoriesForAccount($account);
        } catch (\Exception $e) {
            throw new ListingException(
                'We couldn\'t get a list of categories for your BigCommerce account. Please try again later or contact support if the problem persists',
                $e->getCode(),
                $e
            );
        }
    }

    protected function saveCategories(array $categories, Account $account): void
    {
        $this->categoryImporter->importCategoriesForAccount($categories, $account->getId());
    }
}
