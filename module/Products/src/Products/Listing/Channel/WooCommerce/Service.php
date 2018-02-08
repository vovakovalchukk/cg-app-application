<?php
namespace Products\Listing\Channel\WooCommerce;

use CG\Account\Shared\Entity as Account;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;
use Products\Listing\Category\Service as CategoryService;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface,
    CategoryChildrenInterface
{
    /** @var CategoryService */
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function refetchAndSaveCategories(Account $account)
    {
        return [
            123 => 'new category',
            554 => 'shiny refreshed categ-o-ry'
        ];
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, string $externalCategoryId)
    {
        try {
            return $this->categoryService->fetchCategoryChildrenForAccountAndExternalId($account, $externalCategoryId);
        } catch (NotFound $e) {
            return [];
        }
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'category' => $this->categoryService->fetchCategoriesForAccount($account, 0)
        ];
    }
}
