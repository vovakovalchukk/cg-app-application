<?php
namespace CG\ShipStation\Carrier;

use CG\Account\Shared\Entity as AccountEntity;

interface CarrierSpecificDataInterface
{
    public function getCarrierSpecificData(array $data, AccountEntity $account): ?array;
}