<?php
namespace CG\ShipStation\Request\Connect;

use CG\Account\Client\Entity as Account;
use CG\ShipStation\RequestAbstract;

class Factory
{
    public function buildRequestForAccount(Account $account, array $params): RequestAbstract
    {
        return $this->getClassNameForAccount($account)::fromArray($params);
    }

    protected function getClassNameForAccount(Account $account)
    {
        $channelName = preg_replace('/-ss$/', '', $account->getChannel());
        /** @var ConnectInterface $className */
        $className = __NAMESPACE__ . ucfirst($channelName);
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Channel "' . $account->getChannel() . '" doesn\'t have an associated Connect class');
        }

        return $className;
    }
}
