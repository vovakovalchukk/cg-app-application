<?php
namespace Products\Listing\CreateListings\ChannelService;

use CG\Account\Shared\Entity as Account;

interface ServiceInterface
{
    public function getDefaultSettingsForAccount(Account $account): array;
}
