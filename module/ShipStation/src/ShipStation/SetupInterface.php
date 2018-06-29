<?php
namespace ShipStation;

use CG\Account\Shared\Entity as Account;
use CG\ShipStation\Carrier\Entity as Carrier;
use CG\ShipStation\Credentials;
use Zend\Mvc\Controller\Plugin\Url;

interface SetupInterface
{
    public function __invoke(Carrier $carrier, Url $urlHelper, Account $account = null, Credentials $credentials = null);
}