<?php
namespace CG\Etsy\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;

class CreationService extends CreationServiceAbstract
{
    const CHANNEL = 'etsy';

    public function getChannelName()
    {
        return static::CHANNEL;
    }

    public function configureAccount(AccountEntity $account, array $params)
    {
        // TODO: Implement configureAccount() method.
    }

    public function getDisplayName(array $params)
    {
        // TODO: Implement getDisplayName() method.
    }
}