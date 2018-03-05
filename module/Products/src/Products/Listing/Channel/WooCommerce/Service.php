<?php
namespace Products\Listing\Channel\WooCommerce;

use CG\Account\Shared\Entity as Account;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\WooCommerce\Category\Importer as CategoryImporter;
use CG\WooCommerce\Gearman\Generator\CategoriesImport as CategoriesImportJobGenerator;
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
    /** @var CategoryService */
    protected $categoryService;
    /** @var CategoriesImportJobGenerator */
    protected $categoriesImportJobGenerator;
    /** @var CategoryImporter */
    protected $categoryImporter;

    public function __construct(
        CategoryService $categoryService,
        CategoriesImportJobGenerator $categoriesImportJobGenerator,
        CategoryImporter $categoryImporter
    ) {
        $this->categoryService = $categoryService;
        $this->categoriesImportJobGenerator = $categoriesImportJobGenerator;
        $this->categoryImporter = $categoryImporter;
    }

    public function refetchAndSaveCategories(Account $account)
    {
        try {
            $this->categoryImporter->importAndSaveCategoriesForAccount($account);
        } catch (\Exception $e) {
            throw new ListingException('We are unable to connect to your WooCommerce account. Please try again', $e->getCode(), $e);
        }

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

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'category' => $this->categoryService->fetchRootCategoriesForAccount($account)
        ];
    }
}
