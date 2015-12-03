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
    const LOG_CANCEL_SEND = 'Sending cancel request to Dataplug for Order %s, shipping Account %d';
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
        foreach ($orders as $order) {
            $this->addGlobalLogEventParam('order', $order->getId());
            $orderLabel = $this->getOrderLabelForOrder($order);
            $request = $this->mapper->orderLabelToDataplugCancelRequest($orderLabel);
            $this->logDebug(static::LOG_CANCEL_SEND, [$order->getId(), $shippingAccountId], static::LOG_CODE);
            $this->dataplugClient->sendRequest($request, $shippingAccount);
            $this->cancelOrderLabel($orderLabel);
            $this->removeOrderTracking($order);
        }
        $this->logDebug(static::LOG_CANCEL_DONE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('order')->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');
    }

    protected function cancelOrderLabel(OrderLabel $orderLabel)
    {
        $this->logDebug(static::LOG_UPDATE, [$orderLabel->getOrderId()], static::LOG_CODE);
        $orderLabel->setStatus(OrderLabelStatus::CANCELLED);
        $this->orderLabelService->save($orderLabel);
    }

    protected function removeOrderTracking(Order $order)
    {
        $this->logDebug(static::LOG_REMOVE_TRACKING, [$order->getId()], static::LOG_CODE);
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
