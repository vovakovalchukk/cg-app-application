<?php
namespace CG\CourierExport\RoyalMailClickDrop;

use CG\Channel\Shipping\Provider\Service\ExportDocumentInterface;
use CG\CourierExport\ExporterInterface;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Label\Collection as OrderLabels;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\User\Entity as User;

class Exporter implements ExporterInterface
{
    public function exportOrders(
        Orders $orders,
        OrderLabels $orderLabels,
        array $ordersData,
        array $orderParcelsData,
        array $orderItemsData,
        OrganisationUnit $rootOu,
        User $user
    ): ExportDocumentInterface {
        $export = new Export();
        /** @var OrderLabel $orderLabel */
        foreach ($orderLabels as $orderLabel) {
            $this->addParcelDataToExport(
                $export,
                $orders->getById($orderLabel->getOrderId()),
                $orderLabel,
                $ordersData[$orderLabel->getOrderId()] ?? [],
                $orderParcelsData[$orderLabel->getOrderId()] ?? [],
                $orderItemsData[$orderLabel->getOrderId()] ?? [],
                $rootOu,
                $user
            );
        }
        return $export;
    }

    protected function addParcelDataToExport(
        Export $export,
        Order $order,
        OrderLabel $orderLabel,
        array $ordersData,
        array $orderParcelsData,
        array $orderItemsData,
        OrganisationUnit $rootOu,
        User $user
    ) {
        $export->addRowData(
            [
                'orderReference' => $order->getId(),
                'specialInstructions' => '',
                'date' => '',
                'weight' => '',
                'packageSize' => '',
                'subTotal' => '',
                'shippingCost' => '',
                'total' => '',
                'currencyCode' => '',
                'serviceCode' => '',
                'customerTitle' => '',
                'firstName' => '',
                'lastName' => '',
                'fullName' => '',
                'phone' => '',
                'email' => '',
                'companyName' => '',
                'addressLine1' => '',
                'addressLine2' => '',
                'addressLine3' => '',
                'city' => '',
                'county' => '',
                'postcode' => '',
                'country' => '',
            ]
        );
    }
}