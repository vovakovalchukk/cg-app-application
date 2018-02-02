<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Entity as Account;

interface CategoriesRefreshInterface
{
    public function refetchAndSaveCategories(Account $account);
}
