<?php
namespace CG\ShipStation\ShipStation;

use CG\Account\Client\Service as AccountService;

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