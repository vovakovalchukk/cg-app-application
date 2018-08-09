<?php
namespace CG\ShipStation\Carrier\AccountDecider;

use CG\Account\Shared\Entity as Account;
use CG\ShipStation\Carrier\AccountDeciderInterface;
use CG\ShipStation\ShipStation\Service as ShipStationService;

class Other implements AccountDeciderInterface
{
    /** @var ShipStationService */
    protected $shipStationService;

    public function __construct(ShipStationService $shipStationService)
    {
        $this->shipStationService = $shipStationService;
    }

    public function getShippingAccountForRequests(Account $shippingAccount): Account
    {
        return $shippingAccount;
    }

    public function getShipStationAccountForRequests(Account $shippingAccount): Account
    {
        return $this->shipStationService->getShipStationAccountForShippingAccount($shippingAccount);
    }
}