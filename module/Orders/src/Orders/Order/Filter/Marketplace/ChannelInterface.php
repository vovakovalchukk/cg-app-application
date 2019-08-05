<?php
namespace Orders\Order\Filter\Marketplace;

use CG\Account\Shared\Entity as Account;

interface ChannelInterface
{
    public function __invoke(Account $account): array;
}