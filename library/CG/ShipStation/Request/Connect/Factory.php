<?php
namespace CG\ShipStation\Request\Connect;

use CG\Account\Client\Entity as Account;
use CG\ShipStation\RequestAbstract;

class Factory
{
    const CHANNEL_TO_REQUEST_MAP = [
        'fedex-ss' => FedEx::class
    ];

    public function buildRequestForAccount(Account $account, array $params): RequestAbstract
    {
        return $this->getClassNameForAccount($account)::fromArray($params);
    }

    protected function getClassNameForAccount(Account $account)
    {
        if (!isset(static::CHANNEL_TO_REQUEST_MAP[$account->getChannel()])) {
            throw new \InvalidArgumentException('Channel "' . $account->getChannel() . '" doesn\'t have an associated Connect class');
        }

        return static::CHANNEL_TO_REQUEST_MAP[$account->getChannel()];
    }
}
