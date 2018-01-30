<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Entity as Account;

interface ChannelSpecificValuesInterface
{
    public function getChannelSpecificFieldValues(Account $account): array;
}
