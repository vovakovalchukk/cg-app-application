<?php
namespace CG\Walmart\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Walmart\Credentials;

class CreationService extends CreationServiceAbstract
{
    const CHANNEL = 'walmart';

    public function getChannelName()
    {
        return static::CHANNEL;
    }

    public function getDisplayName(array $params)
    {
        return 'Walmart';
    }

    public function configureAccount(AccountEntity $account, array $params)
    {
        return $account->setCredentials(
            $this->cryptor->encrypt(new Credentials($params['clientId'], $params['clientSecret']))
        );
    }
}