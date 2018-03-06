<?php
namespace Orders\Courier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Service\ExportDocumentInterface;
use CG\Channel\Shipping\Provider\Service\ExportInterface;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Label\Collection as OrderLabels;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Stdlib\Exception\Runtime\NotFound;

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
            $shippingAccount = $this->accountService->fetchShippingAccount($shippingAccountId);
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
                $ordersData,
                $orderParcelsData,
                $ordersItemsData,
                $rootOu,
                $shippingAccount,
                $user
            );
            $this->logDebug(static::LOG_EXPORT_DONE, [implode(',', $orderIds), $shippingAccountId], static::LOG_CODE);

            $this->updateOrderLabelStatus($orderLabels);
        } finally {
            $this->unlockOrderLabels();
            $this->removeGlobalLogEventParams(['ou', 'account']);
        }

        return $export;
    }

    protected function getOrCreateOrderLabelsForOrders(
        Orders $orders,
        array $orderData,
        array $orderParcelsData,
        Account $shippingAccount
    ): OrderLabels {
        try {
            $orderLabels = $this->getOrderLabelsForOrders($orders);
        } catch (NotFound $exception) {
            $orderLabels = new OrderLabels(OrderLabel::class, __FUNCTION__, ['orderId' => $orders->getIds()]);
        }

        $missingOrders = array_diff($orders->getIds(), $orderLabels->getArrayOf('orderId'));
        if (empty($missingOrders)) {
            return $orderLabels;
        }

        foreach ($missingOrders as $missingOrderId) {
            $orderLabels->attach(
                $this->createOrderLabelForOrder(
                    $orders->getById($missingOrderId),
                    $orderData[$missingOrderId] ?? [],
                    $orderParcelsData[$missingOrderId] ?? [],
                    $shippingAccount
                )
            );
        }

        return $orderLabels;
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