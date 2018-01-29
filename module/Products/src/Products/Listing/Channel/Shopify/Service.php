<?php
namespace Products\Listing\Channel\Shopify;

use CG\Account\Shared\Entity as Account;
use CG\Product\Category\Entity as Category;
use CG\Shopify\CustomCollection\Importer as CustomCollectionImporter;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface
{
    /** @var CustomCollectionImporter */
    protected $customCollectionImporter;

    public function __construct(CustomCollectionImporter $customCollectionImporter)
    {
        $this->customCollectionImporter = $customCollectionImporter;
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            "categories" => [
                123  => "category name"
            ]
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
}
