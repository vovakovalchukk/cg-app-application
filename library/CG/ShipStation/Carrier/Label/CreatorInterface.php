<?php
namespace CG\ShipStation\Carrier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\User\Entity as User;

interface CreatorInterface
{
    public function createLabelsForOrders(
        OrderCollection $orders,
        OrderLabelCollection $orderLabels,
        OrderDataCollection $ordersData,
        OrderItemsDataCollection $orderItemsData,
        OrderParcelsDataCollection $orderParcelsData,
        OrganisationUnit $rootOu,
        User $user,
        Account $shippingAccount,
        Account $shipStationAccount
    ): array;
}