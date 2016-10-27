<?php
namespace Orders\Courier\Label;

use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Stdlib\Exception\Runtime\NotFound;

class CancelService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelCancelService';
    const LOG_CANCEL = 'Cancel request for Order(s) %s, shipping Account %d';
    const LOG_CANCEL_DONE = 'Completed cancel request for Order(s) %s, shipping Account %d';
    const LOG_UPDATE = 'Updating OrderLabel to cancelled for Order %s';
    const LOG_REMOVE_TRACKING = 'Removing tracking numbers for Order %s.';

    public function cancelForOrders(array $orderIds, $shippingAccountId)
    {
        $orderIdsString = implode(',', $orderIds);
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParam('account', $shippingAccountId)->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_CANCEL, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $shippingAccount = $this->accountService->fetch($shippingAccountId);
        $orders = $this->getOrdersByIds($orderIds);
        $orderLabels = $this->getOrderLabelsForOrders($orders);

        $this->getCarrierProviderService($shippingAccount)->cancelOrderLabels($orderLabels, $orders, $shippingAccount);
        foreach ($orderLabels as $orderLabel) {
            $order = $orders->getById($orderLabel->getOrderId());
            $this->cancelOrderLabel($orderLabel);
            $this->removeOrderTracking($order);
        }

        $this->logDebug(static::LOG_CANCEL_DONE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');
    }

    protected function cancelOrderLabel(OrderLabel $orderLabel)
    {
        $this->logDebug(static::LOG_UPDATE, [$orderLabel->getOrderId()], static::LOG_CODE, ['order' => $orderLabel->getOrderId()]);
        $orderLabel->setStatus(OrderLabelStatus::CANCELLED);
        $this->orderLabelService->save($orderLabel);
    }

    protected function removeOrderTracking(Order $order)
    {
        $this->logDebug(static::LOG_REMOVE_TRACKING, [$order->getId()], static::LOG_CODE, ['order' => $order->getId()]);
        try {
            $this->orderTrackingService->removeByOrderId($order->getId());
            $orderTrackings = $order->getTrackings();
            $orderTrackings->removeAll($orderTrackings);
            $this->orderTrackingService->createGearmanJob($order);
        } catch (NotFound $e) {
            // No-op
        }
    }
}
