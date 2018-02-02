<?php
namespace Products\Listing\Channel\WooCommerce;

use CG\Account\Shared\Entity as Account;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoriesRefreshInterface,
    CategoryChildrenInterface
{
    public function refetchAndSaveCategories(Account $account)
    {
        return [
            123 => 'new category',
            554 => 'shiny refreshed categ-o-ry'
        ];
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, string $externalCategoryId)
    {
        return [
            333 => 'child C',
            937 => 'another child category'
        ];
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'category' => [
                12366 => 'categ one',
                55386 => 'boots',
                931 => 'test'
            ]
        ];
    }
}
