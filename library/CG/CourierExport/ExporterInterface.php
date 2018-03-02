<?php
namespace CG\CourierExport;

use CG\CourierAdapter\ExportDocumentInterface;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Label\Collection as OrderLabels;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\User\Entity as User;

interface ExporterInterface
{
    /**
     * @param Orders $orders The orders to operate on
     * @param OrderLabels $orderLabels Pre-created OrderLabels to save label PDF data to
     * @param array $ordersData Additional data for each Order:
     *         ['{orderId}' => ['signature' => bool, 'deliveryInstructions' => string, ...]]
     * @param array $orderParcelsData Additional data for each parcel:
     *         ['{orderId}' => ['{parcelIndex}' => ['value' => float, 'height' => float, ...]]]
     * @param array $orderItemsData Additional data for each item:
     *         ['{orderId}' => ['{itemId}' => ['weight' => float, 'hstariff' => string, ...]]]
     * @param OrganisationUnit $rootOu
     * @param User $user The user who triggered the request. Required if creating Order\Trackings
     */
    public function exportOrders(
        Orders $orders,
        OrderLabels $orderLabels,
        array $ordersData,
        array $orderParcelsData,
        array $orderItemsData,
        OrganisationUnit $rootOu,
        User $user
    ): ExportDocumentInterface;
}