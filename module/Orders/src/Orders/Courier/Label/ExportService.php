<?php
namespace Orders\Courier\Label;

use CG\Channel\Shipping\Provider\Service\ExportDocumentInterface;
use CG\Channel\Shipping\Provider\Service\ExportInterface;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Label\Status as OrderLabelStatus;

class ExportService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelExportService';
    const LOG_EXPORT = 'Export request for Order(s) %s, shipping Account %d';
    const LOG_EXPORT_DONE = 'Completed export request for Order(s) %s, shipping Account %d';
    const LOG_UPDATE = 'Updating OrderLabel to exported for Order %s';

    public function exportOrders(
        array $orderIds,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $orderParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        int $shippingAccountId
    ): ExportDocumentInterface {
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $user = $this->userOUService->getActiveUser();
        $this->addGlobalLogEventParams(['ou' => $rootOu->getId(), 'account' => $shippingAccountId]);

        try {
            $shippingAccount = $this->accountService->fetchShippingAccount((int) $shippingAccountId);
            /** @var ExportInterface $carrier */
            $carrier = $this->getCarrierProviderService($shippingAccount);
            $orders = $this->getOrdersByIds($orderIds);

            $this->persistProductDetailsForOrders($orders, $orderParcelsData, $ordersItemsData, $rootOu);
            $orderLabels = $this->getOrCreateOrderLabelsForOrders(
                $orders,
                $ordersData,
                $orderParcelsData,
                $shippingAccount
            );

            $this->logDebug(static::LOG_EXPORT, [implode(',', $orderIds), $shippingAccountId], static::LOG_CODE);
            $export = $carrier->exportOrders(
                $orders,
                $orderLabels,
                $ordersData->toArray(),
                $orderParcelsData->toArray(),
                $ordersItemsData->toArray(),
                $rootOu,
                $shippingAccount,
                $user
            );
            $this->logDebug(static::LOG_EXPORT_DONE, [implode(',', $orderIds), $shippingAccountId], static::LOG_CODE);

            $this->updateOrderLabelStatus($orderLabels, OrderLabelStatus::EXPORTED);
        } finally {
            $this->unlockOrderLabels();
            $this->removeGlobalLogEventParams(['ou', 'account']);
        }

        return $export;
    }
}