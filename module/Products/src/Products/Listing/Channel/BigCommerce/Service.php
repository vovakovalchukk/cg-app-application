<?php
namespace Products\Listing\Channel\BigCommerce;

use CG\Account\Shared\Entity as Account;
use CG\Product\Category\Collection as CategoryCollection;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface,
    CategoryChildrenInterface
{
    /** @var  CategoryService */
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return ['categories' => $this->fetchCategoriesForAccount($account)];
    }

    public function refetchAndSaveCategories(Account $account)
    {
        return [
            223  => "updated category name"
        ];
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, string $externalCategoryId)
    {
        return [
            672 => "child category 1",
            675 => "mambo number 2",
            781 => "and a number 3 please"
        ];
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
                    ->setChannel(['big-commerce'])
                    ->setEnabled(true)
                    ->setListable(true)
            );

            return $this->formatCategoriesResponse($categories);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function filterParentCategoriesOnly(CategoryCollection $categories): void
    {
        /** @var Category $category */
        foreach ($categories as $category) {
            if ($category->getParentId() !== null) {
                $categories->detach($category);
            }
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
}
