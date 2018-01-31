<?php
namespace Products\Listing\Channel\Shopify;

use CG\Account\Shared\Entity as Account;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Category\Entity as Category;
use CG\Shopify\CustomCollection\Importer as CustomCollectionImporter;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface
{
    /** @var CategoryService */
    protected $categoryService;
    /** @var CustomCollectionImporter */
    protected $customCollectionImporter;

    public function __construct(CategoryService $categoryService, CustomCollectionImporter $customCollectionImporter)
    {
        $this->categoryService = $categoryService;
        $this->customCollectionImporter = $customCollectionImporter;
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'categories' => $this->fetchCategoriesForAccount($account)
        ];
    }

    public function refetchAndSaveCategories(Account $account): array
    {
        $categories = $this->customCollectionImporter->fetchImportAndReturnShopifyCategoriesForAccount($account);
        $categoryOptions = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $categoryOptions[$category->getExternalId()] = $category->getTitle();
        }
        return $categoryOptions;
    }

    protected function fetchCategoriesForAccount(Account $account): array
    {
        try {
            $categories = $this->categoryService->fetchCollectionByFilter(
                (new CategoryFilter())
                    ->setLimit('all')
                    ->setPage(1)
                    ->setChannel(['shopify'])
                    ->setAccountId([$account->getId()])
            );
        } catch (NotFound $e) {
            return [];
        }

        $result = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $result[$category->getExternalId()] = $category->getTitle();
        }
        return $result;
    }
}
