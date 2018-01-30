<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Entity as Account;

interface DefaultAccountSettingsInterface
{
    public function getDefaultSettingsForAccount(Account $account): array;
}
