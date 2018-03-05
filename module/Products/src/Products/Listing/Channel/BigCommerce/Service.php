<?php
namespace Products\Listing\Channel\BigCommerce;

use CG\Account\Shared\Entity as Account;
use CG\BigCommerce\Category\Importer as CategoryImporter;
use CG\BigCommerce\Category\Service as BigCommerceCategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Category\Service as CategoryService;
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
        return ['categories' => $this->categoryService->fetchRootCategoriesForAccount($account)];
    }

    public function refetchAndSaveCategories(Account $account)
    {
        $this->saveCategories(
            $this->fetchCategoriesFromBigCommerce($account),
            $account
        );
        return $this->categoryService->fetchRootCategoriesForAccount($account);
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, int $categoryId)
    {
        try {
            return $this->categoryService->fetchCategoryChildrenForAccountAndCategory($categoryId);
        } catch (NotFound $e) {
            return [];
        }
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
