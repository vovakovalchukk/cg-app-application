<?php
namespace ShipStation;

use CG\Account\Shared\Entity as Account;
use CG\ShipStation\Carrier\Entity as Carrier;
use CG\ShipStation\Credentials;

interface SetupInterface
{
    public function __invoke(
        Carrier $carrier,
        int $organisationUnitId,
        Account $account = null,
        Credentials $credentials = null
    );
}