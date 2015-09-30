<?php
namespace Orders\Courier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Dataplug\Request\RetrieveOrders as DataplugGetOrdersRequest;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Stdlib\DateTime as StdlibDateTime;
use Orders\Courier\Label\MissingException as LabelMissingException;
use RuntimeException;
use SimpleXMLElement;

class CreateService extends ServiceAbstract
{
    const LABEL_MAX_ATTEMPTS = 10;
    const LABEL_ATTEMPT_INTERVAL_SEC = 1;

    const LOG_CODE = 'OrderCourierLabelCreateService';
    const LOG_CREATE = 'Create label request for Order(s) %s, shipping Account %d';
    const LOG_CREATE_SEND = 'Sending create request to Dataplug for Order(s) %s, shipping Account %d';
    const LOG_CREATE_UNEXPECTED_RESPONSE = 'Dataplug response from create request missing Order nodes for Order(s) %s, shipping Account %d';
    const LOG_CREATE_MISSING_REF = 'Dataplug response from create request missing Order->OrderNumber for one or more Orders (of set: %s), shipping Account %d';
    const LOG_CREATE_REF = 'Successfully created order(s) with Dataplug and got order number(s) %s for Orders %s, shipping Account %d';
    const LOG_CREATE_DONE = 'Completed create label request for Order(s) %s, shipping Account %d';
    const LOG_GET_LABEL_ATTEMPT = 'Attempt %d to get label data for order number %s, Order %s, shipping Account %d';
    const LOG_GET_LABEL_RETRY = 'No label data found on this attempt, will retry for order number %s, Order %s, shipping Account %d. Giving up.';
    const LOG_GET_LABEL_FAILED = 'Max attempts (%d) to get label data reached for order number %s, Order %s, shipping Account %d. Giving up.';
    const LOG_GET_TRACKING = 'Looking for tracking numbers for order number %s, Order %s, shipping Account %d.';
    const LOG_GET_TRACKING_FOUND = 'Found tracking number %s for Order %s.';
    const LOG_GET_TRACKING_SAVE = 'Saving tracking numbers for Order %s.';
    const LOG_GET = 'Getting order details from Dataplug for reference %s, shipping Account %d';
    const LOG_GET_MISSING_ITEMS = 'Dataplug response from retrieve request missing parcel data for Order %s';
    const LOG_GET_MISSING_LABEL = 'Dataplug response from retrieve request missing label data for Order %s';
    const LOG_SAVE = 'Saving OrderLabel for Order %s';
    const LOG_PDF_MERGE = 'Merging multiple label PDFs into one';
    const LOG_PDF_MERGE_WRITE_FAIL = 'Error writing PDF data to file';
    const LOG_PDF_MERGE_FAIL = 'Error merging PDF data';

    public function createForOrdersData(array $orderIds, array $ordersData, array $orderParcelsData, $shippingAccountId)
    {
        $orderIdsString = implode(',', $orderIds);
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParam('account', $shippingAccountId)->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_CREATE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $shippingAccount = $this->accountService->fetch($shippingAccountId);
        $orders = $this->getOrdersByIds($orderIds);
        $request = $this->mapper->ordersAndDataToDataplugCreateRequest($orders, $ordersData, $orderParcelsData, $rootOu);
        $this->logDebug(static::LOG_CREATE_SEND, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $response = $this->dataplugClient->sendRequest($request, $shippingAccount);
        if (!isset($response->Order)) {
            throw new RuntimeException(vsprintf(static::LOG_CREATE_UNEXPECTED_RESPONSE, [$orderIdsString, $shippingAccountId]));
        }
        $orderNumbers = [];
        foreach ($response->Order as $responseOrder) {
            if (!isset($responseOrder->OrderNumber, $responseOrder->Reference)) {
                throw new RuntimeException(vsprintf(static::LOG_CREATE_MISSING_REF, [$orderIdsString, $shippingAccountId]));
            }
            $orderNumbers[(string)$responseOrder->Reference] = (string)$responseOrder->OrderNumber;
        }
        $this->logDebug(static::LOG_CREATE_REF, [implode(',', $orderNumbers), $orderIdsString, $shippingAccountId], static::LOG_CODE);

        foreach ($orders as $order) {
            $orderNumber = $orderNumbers[$order->getId()];
            $this->getAndProcessDataplugOrderDetails($order, $shippingAccount, $orderNumber);
        }
        $this->logDebug(static::LOG_CREATE_DONE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');
    }

    protected function getAndProcessDataplugOrderDetails(Order $order, Account $shippingAccount, $orderNumber, $attempt = 1)
    {
        try {
            $this->logDebug(static::LOG_GET_LABEL_ATTEMPT, [$attempt, $orderNumber, $order->getId(), $shippingAccount->getId()]);
            $response = $this->getDataplugOrderByOrderNumber($orderNumber, $shippingAccount);
            $labelData = $this->getOrderLabelPdfDataFromRetrieveResponse($order, $response);
            $this->saveLabelDataForAnOrder($order, $orderNumber, $labelData);
            $this->logDebug(static::LOG_GET_TRACKING, [$orderNumber, $order->getId(), $shippingAccount->getId()]);
            $trackingNumbers = $this->getOrderTrackingNumbersFromRetrieveResponse($order, $response);
            $this->saveTrackingNumbersForAnOrder($order, $trackingNumbers, $shippingAccount);

        } catch (LabelMissingException $e) {
            if ($attempt >= static::LABEL_MAX_ATTEMPTS) {
                $this->logError(static::LOG_GET_LABEL_FAILED, [static::LABEL_MAX_ATTEMPTS, $orderNumber, $order->getId(), $shippingAccount->getId()]);
                throw $e;
            }
            $this->logDebug(static::LOG_GET_LABEL_RETRY, [$orderNumber, $order->getId(), $shippingAccount->getId()]);
            sleep(static::LABEL_ATTEMPT_INTERVAL_SEC);
            return $this->getAndProcessDataplugOrderDetails($order, $shippingAccount, $orderNumber, ++$attempt);
        }
    }

    protected function getDataplugOrderByOrderNumber($orderNumber, Account $shippingAccount)
    {
        $this->logDebug(static::LOG_GET, [$orderNumber, $shippingAccount->getId()], static::LOG_CODE);
        $request = new DataplugGetOrdersRequest();
        $request->setSearchType(DataplugGetOrdersRequest::SEARCH_TYPE_ORDER_NUMBER)
            ->setIdentifier($orderNumber)
            ->setMarkPrinted(false);
        return $this->dataplugClient->sendRequest($request, $shippingAccount);
    }

    protected function getOrderLabelPdfDataFromRetrieveResponse(Order $order, SimpleXMLElement $response)
    {
        if (isset($response->Order, $response->Order->Label, $response->Order->Label->Content)) {
            return (string)$response->Order->Label->Content;
        }
        if (!isset($response->Order, $response->Order->ShipmentDetails, $response->Order->ShipmentDetails->Items, $response->Order->ShipmentDetails->Items->Item)) {
            throw new LabelMissingException(vsprintf(static::LOG_GET_MISSING_ITEMS, [$order->getId()]));
        }
        $labelPdfData = [];
        foreach ($response->Order->ShipmentDetails->Items->Item as $parcelDetails)
        {
            if (!isset($parcelDetails->Label->Content)) {
                throw new LabelMissingException(vsprintf(static::LOG_GET_MISSING_LABEL, [$order->getId()]));
            }
            $labelPdfData[] = (string)$parcelDetails->Label->Content;
        }
        return $this->mergePdfData($labelPdfData);
    }

    protected function saveLabelDataForAnOrder(Order $order, $orderNumber, $labelData)
    {
        $this->logDebug(static::LOG_SAVE, [$order->getId()], static::LOG_CODE);
        $date = new StdlibDateTime();
        $orderLabelData = [
            'organisationUnitId' => $order->getOrganisationUnitId(),
            'orderId' => $order->getId(),
            'status' => OrderLabelStatus::NOT_PRINTED,
            'created' => $date->stdFormat(),
            'label' => $labelData,
            'externalId' => $orderNumber,
        ];
        $orderLabel = $this->orderLabelMapper->fromArray($orderLabelData);
        return $this->orderLabelService->save($orderLabel);
    }

    protected function getOrderTrackingNumbersFromRetrieveResponse(Order $order, SimpleXMLElement $response)
    {
        $trackingNumbers = [];
        if (!isset($response->Order, $response->Order->ShipmentDetails, $response->Order->ShipmentDetails->Items, $response->Order->ShipmentDetails->Items->Item)) {
            return $trackingNumbers;
        }
        foreach ($response->Order->ShipmentDetails->Items->Item as $parcelDetails) {
            if (!isset($parcelDetails->TrackingNumber)) {
                continue;
            }
            $trackingNumber = (string)$parcelDetails->TrackingNumber;
            $this->logDebug(static::LOG_GET_TRACKING_FOUND, [$trackingNumber, $order->getId()], static::LOG_CODE);
            $trackingNumbers[] = $trackingNumber;
        }
        return $trackingNumbers;
    }

    protected function saveTrackingNumbersForAnOrder(Order $order, array $trackingNumbers, Account $shippingAccount)
    {
        if (empty($trackingNumbers)) {
            return;
        }
        $this->logDebug(static::LOG_GET_TRACKING_SAVE, [$order->getId()], static::LOG_CODE);
        $date = new StdlibDateTime();
        $orderTrackingData = [
            'organisationUnitId' => $order->getOrganisationUnitId(),
            'orderId' => $order->getId(),
            'userId' => $this->userOUService->getActiveUser()->getId(),
            'timestamp' => $date->stdFormat(),
            'carrier' => $shippingAccount->getDisplayName()
        ];
        foreach ($trackingNumbers as $trackingNumber) {
            $parcelTrackingData = array_merge($orderTrackingData, ['number' => $trackingNumber]);
            $orderTracking = $this->orderTrackingMapper->fromArray($parcelTrackingData);
            $this->orderTrackingService->save($orderTracking);
        }
        $this->orderTrackingService->createGearmanJob($order);
    }

    protected function mergePdfData(array $pdfsData)
    {
        if (count($pdfsData) == 1) {
            return $pdfsData[0];
        }
        $this->logDebug(static::LOG_PDF_MERGE, [], static::LOG_CODE);
        $fileNames = [];
        foreach ($pdfsData as $pdfData) {
            $fileName = '/tmp/label-data-'.microtime(true).'.pdf';
            $result = file_put_contents($fileName, $pdfData);
            if (!$result) {
                throw new RuntimeException(static::LOG_PDF_MERGE_WRITE_FAIL);
            }
            $fileNames[] = $fileName;
        }

        $outputFileName = '/tmp/label-data-merged-'.microtime(true).'.pdf';
        $cmd = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=' . $outputFileName . ' ' . implode(' ', $fileNames);
        $result = shell_exec($cmd);
        if ($result === null) {
            throw new RuntimeException(static::LOG_PDF_MERGE_FAIL);
        }
        $mergedPdfData = file_get_contents($outputFileName);
        unlink($outputFileName);
        foreach ($fileNames as $fileName) {
            unlink($fileName);
        }
        return $mergedPdfData;
    }
}