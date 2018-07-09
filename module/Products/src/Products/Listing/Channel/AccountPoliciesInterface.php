<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Entity as Account;

interface AccountPoliciesInterface
{
    public function refreshAccountPolicies(Account $account): array;
}
