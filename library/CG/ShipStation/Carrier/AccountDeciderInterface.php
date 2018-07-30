<?php
namespace CG\ShipStation\Carrier;

use CG\Account\Shared\Entity as Account;

interface AccountDeciderInterface
{
    public function getShippingAccountForRequests(Account $shippingAccount): Account;
    public function getShipStationAccountForRequests(Account $shippingAccount): Account;
}