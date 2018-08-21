<?php
namespace CG\ShipStation\Carrier\CarrierSpecificData;

use CG\Account\Shared\Entity as AccountEntity;
use CG\ShipStation\Carrier\CarrierSpecificDataInterface;

class Other implements CarrierSpecificDataInterface
{
    public function getCarrierSpecificData(array $data, AccountEntity $account): ?array
    {
        return $data;
    }
}