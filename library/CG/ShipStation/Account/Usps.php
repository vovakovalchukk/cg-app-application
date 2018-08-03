<?php
namespace CG\ShipStation\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountInterface;

class Usps implements AccountInterface
{

    public function getInitialisationUrl(AccountEntity $account, $route)
    {
        // Not required for USPS
        throw new \BadMethodCallException(__METHOD__ . ' not expected to be called');
    }

    public function connect(AccountEntity $account, array $params = []): AccountEntity
    {
        // To be completed in TAC-137
        return $account;
    }
}