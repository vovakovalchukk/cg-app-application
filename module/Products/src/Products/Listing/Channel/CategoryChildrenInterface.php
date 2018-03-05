<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Entity as Account;

interface CategoryChildrenInterface
{
    public function getCategoryChildrenForCategoryAndAccount(Account $account, int $externalCategoryId);
}
