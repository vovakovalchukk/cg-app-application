<?php
namespace Orders\Order\Filter\Marketplace;

use CG\Account\Shared\Entity as Account;
use CG_UI\View\Filters\SelectOptions\TitleValue;

interface ChannelInterface
{
    /**
     * @return TitleValue[] keyed on value
     */
    public function __invoke(Account $account): array;
}