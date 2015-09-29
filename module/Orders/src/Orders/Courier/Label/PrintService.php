<?php
namespace Orders\Courier\Label;

class PrintService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelPrintService';
    const LOG_PRINT = 'Print request for Order %s';
    const LOG_PRINT_DONE = 'Completed print request for Order %s';

    public function getPdfLabelDataForOrder($orderId)
    {
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParam('order', $orderId)->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_PRINT, [$orderId], static::LOG_CODE);
        $order = $this->orderService->fetch($orderId);
        $orderLabel = $this->getOrderLabelForOrder($order);
        $pdfData = base64_decode($orderLabel->getLabel());
        $this->logDebug(static::LOG_PRINT_DONE, [$orderId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('order')->removeGlobalLogEventParam('ou');
        return $pdfData;
    }
}