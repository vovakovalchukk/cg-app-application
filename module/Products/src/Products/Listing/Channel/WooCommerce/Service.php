<?php
namespace Products\Listing\Channel\WooCommerce;

use CG\Account\Shared\Entity as Account;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\WooCommerce\Gearman\Generator\CategoriesImport as CategoriesImportJobGenerator;
use Products\Listing\Category\Service as CategoryService;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface,
    CategoryChildrenInterface
{
    /** @var CategoryService */
    protected $categoryService;
    /** @var  CategoriesImportJobGenerator */
    protected $categoriesImportJobGenerator;

    public function __construct(
        CategoryService $categoryService,
        CategoriesImportJobGenerator $categoriesImportJobGenerator
    ) {
        $this->categoryService = $categoryService;
        $this->categoriesImportJobGenerator = $categoriesImportJobGenerator;
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
