<?php
namespace CG\ManualOrder\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Account\Credentials\Cryptor;
use CG\ManualOrder\Credentials;

class CreationService extends CreationServiceAbstract
{
    const CHANNEL = 'manual-order';

    public function configureAccount(AccountEntity $account, array $params)
    {
        $account->setCredentials($this->cryptor->encrypt(new Credentials()));
    }

    public function getChannelName()
    {
        return static::CHANNEL;
    }

    public function getDisplayName(array $params)
    {
        return 'Manual Orders';
    }

    protected function setCryptor(Cryptor $cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }
}