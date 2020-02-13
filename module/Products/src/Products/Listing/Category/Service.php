<?php
namespace Products\Listing\Category;

use CG\Account\Shared\Entity as Account;
use CG\Product\Category\Collection as CategoryCollection;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    /** @var  CategoryService */
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function fetchCategoriesForAccount(
        Account $account,
        int $parentId = null,
        ?bool $listable = true,
        string $marketplace = null,
        bool $useAccountId = true
    ): array {
        $filter = (new CategoryFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setChannel([$account->getChannel()])
            ->setEnabled(true)
            ->setListable($listable);

        !is_null($parentId) ? $filter->setParentId([$parentId]) : null;
        !is_null($marketplace) ? $filter->setMarketplace([$marketplace]) : null;
        $useAccountId ? $filter->setAccountId([$account->getId()]) : null;

        try {
            /** @var CategoryCollection $categories */
            $categories = $this->categoryService->fetchCollectionByFilter($filter);
            return $this->formatCategoriesResponse($categories);
        } catch (NotFound $e) {
            return [];
        }
    }

    public function fetchRootCategoriesForAccount(
        Account $account,
        ?bool $listable = true,
        string $marketplace = null,
        bool $useAccountId = true
    ) {
        return $this->fetchCategoriesForAccount($account, 0, $listable, $marketplace, $useAccountId);
    }

    public function fetchCategoryChildrenForParentCategoryId(int $categoryId) {
        try {
            $filter = (new CategoryFilter('all', 1))->setParentId([$categoryId]);
            $categories = $this->categoryService->fetchCollectionByFilter($filter);
            return $this->formatCategoriesResponse($categories);
        } catch (NotFound $e) {
            return [];
        }
    }

    public function fetch(int $categoryId): Category
    {
        return $this->categoryService->fetch($categoryId);
    }

    public function formatCategoriesResponse(CategoryCollection $categoryCollection): array
    {
        $categories = [];
        /** @var Category $category */
        foreach ($categoryCollection as $category) {
            $categories[$category->getId()] = [
                'title' => $category->getTitle(),
                'listable' => $category->isListable(),
            ];
        }
        return $categories;
    }
}
