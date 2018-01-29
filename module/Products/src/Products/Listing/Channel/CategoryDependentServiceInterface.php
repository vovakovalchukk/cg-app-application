<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Entity as Account;

interface CategoryDependentServiceInterface
{
    public function getCategoryDependentValues(Account $account, string $externalCategoryId): array;
}
