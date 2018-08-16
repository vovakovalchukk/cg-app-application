<?php
namespace CG\ShipStation\Carrier\BookingOption;

use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\Account\Shared\Entity as AccountEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;

class Other implements BookingOptionInterface
{

    public function getOptionsDataForOption(
        $option,
        OrderEntity $order,
        AccountEntity $account,
        $service,
        OrganisationUnit $rootOu,
        ProductDetailCollection $productDetails
    ): ?array
    {
        return [];
    }
}