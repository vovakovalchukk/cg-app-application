<?php
namespace Orders\Order\Filter\Marketplace\Channel;

use CG\Account\Shared\Entity as Account;
use CG_UI\View\Filters\SelectOptions\TitleValue;
use Orders\Order\Filter\Marketplace\ChannelInterface;

class Etsy implements ChannelInterface
{
    public function __invoke(Account $account): array
    {
        $marketplaces = unserialize($account->getExternalDataByKey('marketplace'));
        $options = [];
        if (empty($marketplaces)) {
            return $options;
        }
        foreach ($marketplaces as $id => $name) {
            $options[$id] = new TitleValue('Etsy ' . $name, $id);
        }
        return $options;
    }
}