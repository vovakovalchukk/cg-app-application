<?php
namespace CG\ShipStation\Carrier\AccountDecider;

use CG\Account\Shared\Entity as Account;
use CG\ShipStation\Carrier\AccountDeciderInterface;
use CG\ShipStation\Account\Usps\Mapper;

class Usps implements AccountDeciderInterface
{
    /** @var Mapper */
    protected $mapper;

    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getShippingAccountForRequests(Account $shippingAccount): Account
    {
        return $this->mapper->cgShippingAccountFromShippingAccount($shippingAccount);
    }

    public function getShipStationAccountForRequests(Account $shippingAccount): Account
    {
        return $this->mapper->cgShipStationAccountFromShippingAccount($shippingAccount);
    }
}