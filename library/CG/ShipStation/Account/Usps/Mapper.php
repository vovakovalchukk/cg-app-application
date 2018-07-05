<?php
namespace CG\ShipStation\Account\Usps;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Mapper as AccountMapper;
use CG\Channel\Type as AccountType;
use CG\ShipStation\Account\Usps\ChannelGrabberAccount;
use CG\ShipStation\ShipStation\ChannelGrabberCredentials;

class Mapper
{
    /** @var Cryptor */
    protected $cryptor;
    /** @var AccountMapper */
    protected $accountMapper;

    public function __construct(Cryptor $cryptor, AccountMapper $accountMapper)
    {
        $this->cryptor = $cryptor;
        $this->accountMapper = $accountMapper;
    }

    public function cgShipStationAccountFromShippingAccount(Account $shippingAccount): Account
    {
        $credentials = $this->cryptor->encrypt(new ChannelGrabberCredentials());
        return $this->accountMapper->fromArray([
            'channel' => 'shipstationAccount',
            'organisationUnitId' => $shippingAccount->getOrganisationUnitId(),
            'displayName' => 'ShipStation',
            'credentials' => $credentials,
            'active' => true,
            'deleted' => false,
            'pending' => false,
            'type' => [AccountType::SHIPPING_PROVIDER],
        ]);
    }

    public function cgShippingAccountFromShippingAccount(Account $shippingAccount): Account
    {
        $cgShippingAccount = $this->accountMapper->fromArray($shippingAccount->toArray());
        $cgShippingAccount->setExternalId(ChannelGrabberAccount::EXTERNAL_ID);
        return $cgShippingAccount;
    }
}