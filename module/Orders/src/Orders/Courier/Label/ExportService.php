<?php
namespace Orders\Courier\Label;

use CG\Channel\Shipping\Provider\Service\ExportInterface;
use CG\CourierAdapter\ExportDocumentInterface;
use CG\Order\Shared\Label\Collection as OrderLabels;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Status as OrderLabelStatus;

class ExportService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelExportService';
    const LOG_EXPORT = 'Export request for Order(s) %s, shipping Account %d';
    const LOG_EXPORT_DONE = 'Completed export request for Order(s) %s, shipping Account %d';
    const LOG_UPDATE = 'Updating OrderLabel to exported for Order %s';

    public function exportOrders(
        array $orderIds,
        array $ordersData,
        array $orderParcelsData,
        array $ordersItemsData,
        int $shippingAccountId
    ): ExportDocumentInterface {
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $user = $this->userOUService->getActiveUser();
        $this->addGlobalLogEventParams(['ou' => $rootOu->getId(), 'account' => $shippingAccountId]);

        try {
            $shippingAccount = $this->accountService->fetch($shippingAccountId);
            /** @var ExportInterface $carrier */
            $carrier = $this->getCarrierProviderService($shippingAccount);
            $orders = $this->getOrdersByIds($orderIds);
            $orderLabels = $this->getOrderLabelsForOrders($orders);

            $this->logDebug(static::LOG_EXPORT, [implode(',', $orderIds)], static::LOG_CODE, $shippingAccountId);
            $export = $carrier->exportOrders(
                $orders,
                $orderLabels,
                $ordersData,
                $orderParcelsData,
                $ordersItemsData,
                $rootOu,
                $shippingAccount,
                $user
            );
            $this->logDebug(static::LOG_EXPORT_DONE, [implode(',', $orderIds)], static::LOG_CODE, $shippingAccountId);

            $this->updateOrderLabelStatus($orderLabels);
        } finally {
            $this->removeGlobalLogEventParams(['ou', 'account']);
        }

        return $export;
    }

    protected function updateOrderLabelStatus(OrderLabels $orderLabels)
    {
        /** @var OrderLabel $orderLabel */
        foreach ($orderLabels as $orderLabel) {
            $this->logDebug(static::LOG_UPDATE, [$orderLabel->getOrderId()], static::LOG_CODE, ['order' => $orderLabel->getOrderId()]);
            $orderLabel->setStatus(OrderLabelStatus::EXPORTED);
            $this->orderLabelService->save($orderLabel);
        }
    }
}