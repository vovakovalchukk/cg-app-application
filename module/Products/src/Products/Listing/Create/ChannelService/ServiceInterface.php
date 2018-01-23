<?php
namespace Products\Listing\Create\ChannelService;

use CG\Account\Shared\Entity as Account;

interface ServiceInterface
{
    public function getDefaultSettingsForAccount(Account $account): array;
}
