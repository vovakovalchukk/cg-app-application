<?php
namespace Products\Listing\Channel\Shopify;

use CG\Account\Shared\Entity as Account;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface
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
            456  => "updated category name"
        ];
    }
}
