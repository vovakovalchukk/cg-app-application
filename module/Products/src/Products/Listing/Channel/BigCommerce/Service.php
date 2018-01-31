<?php
namespace Products\Listing\Channel\BigCommerce;

use CG\Account\Shared\Entity as Account;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface,
    CategoryChildrenInterface
{
    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            "categories" => [
                123  => "category name"
            ]
        ];
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
}
