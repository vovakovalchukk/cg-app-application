<?php
namespace CG\ShipStation\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Account\Credentials\Cryptor;
use CG\Channel\Type as ChannelType;
use CG\ShipStation\Account;
use CG\ShipStation\Credentials;

/**
 * Class CreationService
 * @package CG\ShipStation\Account
 * @method Cryptor getCryptor()
 * @method Account getChannelAccount()
 */
class CreationService extends CreationServiceAbstract
{
    public function configureAccount(AccountEntity $account, array $params)
    {
        /** @TODO: For FedEx, this will be fedex-ss. How do we get the display name to be FedEx? */
        $channel = $params['channel'];
        $credentials = new Credentials();
        foreach ($params as $field => $value) {
            $credentials->set($field, $value);
        }

        $account->setType([ChannelType::SHIPPING])
            ->setChannel($params['channel'])
            ->setDisplayName($channel)
            ->setCredentials($this->getCryptor()->encrypt($credentials));
        return $this->getChannelAccount()->connect($account, $params);
    }

    /**
     * @return string
     * The channel name is handled by @configureAccount method
     */
    public function getChannelName()
    {
        return '';
    }

    /**
     * @param array $params
     * @return string
     * The channel display name is handled by @configureAccount method
     */
    public function getDisplayName(array $params)
    {
        return '';
    }
}
