<?php
namespace Orders\Courier\Label;

use CG\Order\Shared\Label\Status as OrderLabelStatus;

class ReadyService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelReadyService';
    const LOG_CHECK = 'Ready check request for Order(s) %s';
    const LOG_CHECK_DONE = 'Completed ready check request for Order(s) %s, the folowing are ready: %s';

    public function checkForOrders(array $orderIds)
    {
        $orderIdsString = implode(',', $orderIds);
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_CHECK, [$orderIdsString], static::LOG_CODE);
        $orders = $this->getOrdersByIds($orderIds);
        $orderLabels = $this->getOrderLabelsForOrders($orders);
        $readyOrderIds = [];
        foreach ($orderLabels as $orderLabel) {
            if ($orderLabel->getStatus() == OrderLabelStatus::CREATING) {
                continue;
            }
            $readyOrderIds[] = $orderLabel->getOrderId();
        }
        $this->logDebug(static::LOG_CHECK_DONE, [$orderIdsString, implode(',', $readyOrderIds)], static::LOG_CODE);
        $this->removeGlobalLogEventParam('ou');
        return $readyOrderIds;
    }
}