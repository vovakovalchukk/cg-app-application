<?php
namespace CG\Etsy\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Etsy\Credentials;

class CreationService extends CreationServiceAbstract
{
    const CHANNEL = 'etsy';

    public function getChannelName()
    {
        return static::CHANNEL;
    }

    public function configureAccount(AccountEntity $account, array $params)
    {
        return $account->setCredentials(
            $this->cryptor->encrypt(new Credentials($params['accessToken'] ?? null))
        );
    }

    public function getDisplayName(array $params)
    {
        return $params['loginName'] ?? static::CHANNEL;
    }
}