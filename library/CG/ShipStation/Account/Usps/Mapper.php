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
        $shippingAccountCreds = $this->cryptor->decrypt($shippingAccount->getCredentials());
        $shipStationAccountCreds = $this->cryptor->encrypt(new ChannelGrabberCredentials());
        return $this->accountMapper->fromArray([
            'channel' => 'shipstationAccount',
            'organisationUnitId' => $shippingAccount->getOrganisationUnitId(),
            'displayName' => 'ShipStation',
            'credentials' => $shipStationAccountCreds,
            'active' => true,
            'deleted' => false,
            'pending' => false,
            'type' => [AccountType::SHIPPING_PROVIDER],
            // For USPS we store the warehouseId in the credentials, copy it over
            'externalData' => ['warehouseId' => ($shippingAccountCreds->has('warehouseId') ? $shippingAccountCreds->get('warehouseId') : null)],
        ]);
    }

    public function cgShippingAccountFromShippingAccount(Account $shippingAccount): Account
    {
        $cgShippingAccount = $this->accountMapper->fromArray($shippingAccount->toArray());
        $cgShippingAccount->setExternalId(ChannelGrabberAccount::EXTERNAL_ID);
        return $cgShippingAccount;
    }
}