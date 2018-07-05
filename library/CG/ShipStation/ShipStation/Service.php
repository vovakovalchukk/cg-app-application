<?php
namespace CG\ShipStation\ShipStation;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;

class Service
{
    /** @var AccountService */
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function getShipStationAccountForShippingAccount(Account $shippingAccount): Account
    {
        $shipStationAccountId = $shippingAccount->getExternalDataByKey('shipstationAccountId');
        return $this->accountService->fetch($shipStationAccountId);
    }
}