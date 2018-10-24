<?php
namespace ShipStation;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Carrier\Entity as Carrier;
use CG\ShipStation\Credentials;
use Zend\View\Model\ViewModel;

interface SetupInterface
{
    public function __invoke(
        Carrier $carrier,
        int $organisationUnitId,
        Account $account = null,
        Credentials $credentials = null
    ): ViewModel;
}