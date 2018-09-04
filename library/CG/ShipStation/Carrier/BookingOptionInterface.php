<?php
namespace CG\ShipStation\Carrier;

use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\Account\Shared\Entity as AccountEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;

interface BookingOptionInterface
{
    public function getOptionsDataForOption(
        $option,
        OrderEntity $order,
        AccountEntity $account,
        $service,
        OrganisationUnit $rootOu,
        ProductDetailCollection $productDetails
    ): ?array;
}