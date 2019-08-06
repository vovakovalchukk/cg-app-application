<?php
namespace Orders\Order\Filter\Marketplace\Channel;

use CG\Amazon\RegionFactory;
use CG\Account\Shared\Entity as Account;
use CG_UI\View\Filters\SelectOptions\TitleValue;
use Orders\Order\Filter\Marketplace\ChannelInterface;

class Amazon implements ChannelInterface
{
    /** @var RegionFactory */
    protected $regionFactory;

    public function __construct(RegionFactory $regionFactory)
    {
        $this->regionFactory = $regionFactory;
    }

    public function __invoke(Account $account): array
    {
        $regionCode = $account->getExternalDataByKey('regionCode');
        $regionClass = $this->regionFactory->getByRegionCode($regionCode);
        $marketplaces = array_keys($regionClass->getMarketplaces());
        $options = [];
        foreach ($marketplaces as $marketplace) {
            $options[] = new TitleValue('Amazon ' . $marketplace, $marketplace);
        }
        return $options;
    }
}