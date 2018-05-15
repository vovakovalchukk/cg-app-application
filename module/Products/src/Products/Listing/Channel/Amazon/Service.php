<?php
namespace Products\Listing\Channel\Amazon;

use CG\Account\Shared\Entity as Account;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Category\Service as CategoryService;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoryChildrenInterface
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
            'categories' => $this->categoryService->fetchRootCategoriesForAccount(
                $account,
                true,
                null,
                false
            )
        ];
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, int $categoryId)
    {
        try {
            return $this->categoryService->fetchCategoryChildrenForParentCategoryId($categoryId);
        } catch (NotFound $e) {
            return [];
        }
    }
}
