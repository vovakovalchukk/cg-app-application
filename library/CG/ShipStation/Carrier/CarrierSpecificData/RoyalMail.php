<?php
namespace CG\ShipStation\Carrier\CarrierSpecificData;

use CG\Account\Shared\Entity as AccountEntity;
use CG\ShipStation\Carrier\CarrierSpecificDataInterface;

class RoyalMail implements CarrierSpecificDataInterface
{
    public function getCarrierSpecificData(array $data, AccountEntity $account): ?array
    {
//        foreach ($data as &$row) {
//
//
//            $row['packageTypes'] = $packageTypes;
//        }
        return $data;
    }
}