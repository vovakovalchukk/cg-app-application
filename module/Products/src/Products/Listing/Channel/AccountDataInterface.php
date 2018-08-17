<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Entity as Account;

interface AccountDataInterface
{
    public function getAccountData(Account $account): array;
}
