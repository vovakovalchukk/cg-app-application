<?php
namespace Orders\Courier\Label;

use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Stdlib;

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
        foreach ($orderIds as $orderId) {
            $labels = $orderLabels->getBy('orderId', $orderId);
            $labels->rewind();

            /** @var OrderLabel $orderLabel */
            if ($orderLabel = $labels->current()) {
                $pdfsData[] = base64_decode($orderLabel->getLabel());
            }
        }
        $pdfData = Stdlib\mergePdfData($pdfsData);
        $this->logDebug(static::LOG_PRINT_DONE, [$orderIdsString], static::LOG_CODE);
        $this->removeGlobalLogEventParam('ou');
        return $pdfData;
    }
}
