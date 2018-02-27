<?php
namespace Products\Listing\Channel\Shopify;

use CG\Account\Shared\Entity as Account;
use CG\Product\Category\Collection as CategoryCollection;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Shopify\Client\ThrottledException;
use CG\Shopify\Client\UnauthorizedException;
use CG\Shopify\CustomCollection\Importer as CustomCollectionImporter;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;
use Products\Listing\Exception as ListingException;

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

        return $this->getOptionsForCategories($categories);
    }

    protected function getOptionsForCategories(CategoryCollection $categories): array
    {
        $categoryOptions = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $categoryOptions[$category->getExternalId()] = [
                'title' => $category->getTitle()
            ];
        }
        return $categoryOptions;
    }

    public function refetchAndSaveCategories(Account $account): array
    {
        $categories = $this->fetchImportAndReturnShopifyCategoriesForAccount($account);
        return $this->getOptionsForCategories($categories);
    }

    protected function fetchImportAndReturnShopifyCategoriesForAccount(Account $account): CategoryCollection
    {
        try {
            return $this->customCollectionImporter->fetchImportAndReturnShopifyCategoriesForAccount($account);

        } catch (UnauthorizedException $e) {
            throw new ListingException(
                'We are unable to connect to your Shopify account. Please open the account page and click \'Renew Connection\'',
                $e->getCode(),
                $e
            );
        } catch (ThrottledException $e) {
            throw new ListingException(
                'Shopify limit the amount of requests we can make and we\'ve run into their limits when trying to refresh your categories. Please try again.',
                $e->getCode(),
                $e
            );
        }
    }
}
