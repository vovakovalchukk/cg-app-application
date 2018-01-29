<?php
namespace Products\Listing\Channel\Shopify;

use CG\Account\Shared\Entity as Account;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface
{
    /** @var CategoryService */
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'categories' => $this->fetchCategoriesForAccount($account)
        ];
    }

    public function refetchAndSaveCategories(Account $account)
    {
        return [
            456  => "updated category name"
        ];
    }

    protected function fetchCategoriesForAccount(Account $account)
    {
        $categories = $this->categoryService->fetchCollectionByFilter(
            (new CategoryFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setChannel(['shopify'])
                ->setAccountId([$account->getId()])
        );
        $result = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $result[$category->getExternalId()] = $category->getTitle();
        }
        return $result;
    }
}
