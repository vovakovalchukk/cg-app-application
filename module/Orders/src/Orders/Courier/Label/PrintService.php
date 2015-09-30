<?php
namespace Orders\Courier\Label;

class PrintService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelPrintService';
    const LOG_PRINT = 'Print request for Order(s) %s';
    const LOG_PRINT_DONE = 'Completed print request for Order(s) %s';

    public function getPdfLabelDataForOrders(array $orderIds)
    {
        $orderIdsString = implode(',', $orderIds);
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_PRINT, [$orderIdsString], static::LOG_CODE);
        $orders = $this->getOrdersByIds($orderIds);
        $orderLabels = $this->getOrderLabelsForOrders($orders);
        $pdfsData = [];
        foreach ($orderLabels as $orderLabel) {
            $pdfsData[] = base64_decode($orderLabel->getLabel());
        }
        $pdfData = $this->mergePdfData($pdfsData);
        $this->logDebug(static::LOG_PRINT_DONE, [$orderIdsString], static::LOG_CODE);
        $this->removeGlobalLogEventParam('ou');
        return $pdfData;
    }
}