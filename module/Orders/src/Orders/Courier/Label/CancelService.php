<?php
namespace Orders\Courier\Label;

use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Status as OrderLabelStatus;

class CancelService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelCancelService';
    const LOG_CANCEL = 'Cancel request for Order %s, shipping Account %d';
    const LOG_CANCEL_SEND = 'Sending cancel request to Dataplug for Order %s, shipping Account %d';
    const LOG_CANCEL_DONE = 'Completed cancel request for Order %s, shipping Account %d';
    const LOG_UPDATE = 'Updating OrderLabel to cancelled for Order %s';
    const LOG_REMOVE_TRACKING = 'Removing tracking numbers for Order %s.';

    public function cancelForOrderData($orderId, $shippingAccountId)
    {
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParam('order', $orderId)->addGlobalLogEventParam('account', $shippingAccountId)->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_CANCEL, [$orderId, $shippingAccountId], static::LOG_CODE);
        $shippingAccount = $this->accountService->fetch($shippingAccountId);
        $order = $this->orderService->fetch($orderId);
        $orderLabel = $this->getOrderLabelForOrder($order);
        $request = $this->mapper->orderLabelToDataplugCancelRequest($orderLabel);
        $this->logDebug(static::LOG_CANCEL_SEND, [$orderId, $shippingAccountId], static::LOG_CODE);
        $this->dataplugClient->sendRequest($request, $shippingAccount);
        $this->cancelOrderLabel($orderLabel);
        $this->removeOrderTracking($order);
        $this->logDebug(static::LOG_CANCEL_DONE, [$orderId, $shippingAccountId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('order')->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');
    }

    protected function getOrderLabelForOrder(Order $order)
    {
        $labelStatuses = OrderLabelStatus::getAllStatuses();
        $labelStatusesNotCancelled = array_diff($labelStatuses, [OrderLabelStatus::CANCELLED]);
        $filter = (new OrderLabelFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setOrderId([$order->getId()])
            ->setStatus($labelStatusesNotCancelled);
        $orderLabels = $this->orderLabelService->fetchCollectionByFilter($filter);
        $orderLabels->rewind();
        return $orderLabels->current();
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
        $this->orderTrackingService->removeByOrderId($order->getId());
        $orderTrackings = $order->getTrackings();
        $orderTrackings->removeAll($orderTrackings);
        $this->orderTrackingService->createGearmanJob($order);
    }
}
