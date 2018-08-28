<?php
namespace CG\ShipStation\Carrier\Label\Creator;

use CG\Account\Shared\Entity as Account;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Carrier\Label\CreatorInterface;
use CG\ShipStation\Client as ShipStationClient;
use CG\ShipStation\Messages\Exception\InvalidStateException;
use CG\ShipStation\Request\Shipping\Label as LabelRequest;
use CG\ShipStation\Request\Shipping\Shipments as ShipmentsRequest;
use CG\ShipStation\Response\Shipping\Label as LabelResponse;
use CG\ShipStation\Response\Shipping\Shipment;
use CG\ShipStation\Response\Shipping\Shipments as ShipmentsResponse;
use CG\ShipStation\ShippingService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\MultiTransferException;
use Guzzle\Http\Message\Request as GuzzleRequest;

class Other implements CreatorInterface, LoggerAwareInterface
{
    use LogTrait;

    const LABEL_FORMAT = 'pdf';
    const LABEL_SAVE_MAX_ATTEMPTS = 2;
    const PDF_DOWNLOAD_MAX_ATTEMPTS = 2;
    const LOG_CODE = 'ShipStationLabelCreator';

    /** @var ShipStationClient */
    protected $shipStationClient;
    /** @var GuzzleClient */
    protected $guzzleClient;
    /** @var OrderLabelService */
    protected $orderLabelService;
    /** @var ShippingService */
    protected $shippingService;

    protected $testLabelBlacklist = ['fedex-ss', 'ups-ss', 'royal-mail-ss'];

    public function __construct(
        ShipStationClient $shipStationClient,
        GuzzleClient $guzzleClient,
        OrderLabelService $orderLabelService,
        ShippingService $shippingService
    ) {
        $this->shipStationClient = $shipStationClient;
        $this->guzzleClient = $guzzleClient;
        $this->orderLabelService = $orderLabelService;
        $this->shippingService = $shippingService;
    }

    public function createLabelsForOrders(
        OrderCollection $orders,
        OrderLabelCollection $orderLabels,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $orderParcelsData,
        OrganisationUnit $rootOu,
        Account $shippingAccount,
        Account $shipStationAccount
    ): array {
        $this->addGlobalLogEventParams(['ou' => $shippingAccount->getOrganisationUnitId(), 'rootOu' => $rootOu->getId(), 'account' => $shippingAccount->getId()]);
        $this->logInfo('Create labels request for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'Start']);

        $this->injectSignatureRequiredData($ordersData);
        $shipments = $this->createShipmentsForOrders($orders, $ordersData, $orderParcelsData, $shipStationAccount, $shippingAccount, $rootOu);
        $shipmentErrors = $this->getErrorsForFailedShipments($shipments);
        $labels = $this->createLabelsForSuccessfulShipments($shipments, $shipStationAccount, $shippingAccount);
        $labelErrors = $this->getErrorsForUnsuccessfulLabels($labels);
        $labelPdfs = $this->downloadPdfsForLabels($labels);
        $pdfErrors = $this->getErrorsForFailedPdfs($labelPdfs);
        $errors = array_merge($shipmentErrors, $labelErrors, $pdfErrors);
        $this->updateOrderLabels($orderLabels, $labels, $labelPdfs, $errors);

        $this->logInfo('Labels created for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'End']);
        $this->removeGlobalLogEventParams(['ou', 'rootOu', 'account']);

        return $this->buildResponseArray($orders, $errors);
    }

    protected function injectSignatureRequiredData(OrderDataCollection $ordersData): void
    {
        /** @var OrderData $orderData */
        foreach ($ordersData as $orderData) {
            if ($this->shippingService->doesServiceRequireSignature($orderData->getService())) {
                $orderData->setSignature(true);
            }
        }
    }

    protected function createShipmentsForOrders(
        OrderCollection $orders,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $orderParcelsData,
        Account $shipStationAccount,
        Account $shippingAccount,
        OrganisationUnit $rootOu
    ): ShipmentsResponse {
        try {
            $this->logDebug('Creating shipments for %d orders', [count($orders)], [static::LOG_CODE, 'Shipments']);
            $request = ShipmentsRequest::createFromOrdersAndData(
                $orders, $ordersData, $orderParcelsData, $shipStationAccount, $shippingAccount, $rootOu
            );
            return $this->shipStationClient->sendRequest($request, $shipStationAccount);
        } catch (InvalidStateException $e) {
            $this->logWarningException($e, $e->getMessage(), [], [static::LOG_CODE, 'InvalidUSState']);
            throw $e;
        }
    }

    protected function getErrorsForFailedShipments(ShipmentsResponse $shipments): array
    {
        $errors = [];
        /** @var Shipment $shipment */
        foreach ($shipments as $shipment) {
            if (empty($shipment->getErrors())) {
                continue;
            }
            $errors[$shipment->getOrderId()] = $shipment->getErrors();
            $this->logNotice('Failed to create shipment for Order %s', ['order' => $shipment->getOrderId()], [static::LOG_CODE, 'ShipmentFail']);
        }
        return $errors;
    }

    protected function createLabelsForSuccessfulShipments(
        ShipmentsResponse $shipments,
        Account $shipStationAccount,
        Account $shippingAccount
    ): array {
        $this->logDebug('Requesting labels for shipments', [], [static::LOG_CODE, 'Labels']);
        $labels = [];
        /** @var Shipment $shipment */
        foreach ($shipments as $shipment) {
            if (!empty($shipment->getErrors())) {
                continue;
            }
            $request = new LabelRequest($shipment->getShipmentId(), static::LABEL_FORMAT, $this->isTestLabel($shippingAccount));
            $labels[$shipment->getOrderId()] = $this->shipStationClient->sendRequest($request, $shipStationAccount);
        }
        return $labels;
    }

    protected function isTestLabel(Account $shippingAccount): bool
    {
        if (ENVIRONMENT == 'live') {
            return false;
        }
        if (in_array($shippingAccount->getChannel(), $this->testLabelBlacklist)) {
            return false;
        }
        return true;
    }

    protected function getErrorsForUnsuccessfulLabels(array $labelResponses): array
    {
        $errors = [];
        /** @var LabelResponse $labelResponse */
        foreach ($labelResponses as $orderId => $labelResponse) {
            if (empty($labelResponse->getErrors())) {
                continue;
            }
            $errors[$orderId] = $labelResponse->getErrors();
            $this->logNotice('Failed to create label for Order %s', ['order' => $orderId], [static::LOG_CODE, 'LabelFail']);
        }
        return $errors;
    }

    protected function downloadPdfsForLabels(array $labels): array
    {
        $this->logDebug('Downloading PDFs for labels', [], [static::LOG_CODE, 'Pdfs']);
        $labelPdfs = [];
        $requests = [];
        /** @var LabelResponse $label */
        foreach ($labels as $orderId => $label) {
            if (!empty($label->getErrors())) {
                continue;
            }
            $requests[$orderId] = $this->guzzleClient->get($label->getLabelDownload()->getHref());
        }

        return $this->sendLabelPdfDownloadRequests($requests);
    }

    protected function sendLabelPdfDownloadRequests(array $requests, $attempt = 1): array
    {
        try {
            $this->guzzleClient->send($requests);
            return $this->getLabelPdfsFromRequests($requests);
        } catch (MultiTransferException $e) {
            $labelPdfs = $this->getLabelPdfsFromRequests($requests, $e->getFailedRequests());
            if ($attempt < static::PDF_DOWNLOAD_MAX_ATTEMPTS) {
                $labelPdfsRetry = $this->sendLabelPdfDownloadRequests($e->getFailedRequests(), ++$attempt);
                $labelPdfs = array_merge($labelPdfs, $labelPdfsRetry);
            }
            return $labelPdfs;
        }
    }

    protected function getLabelPdfsFromRequests(array $requests, array $failedRequests = []): array
    {
        $labelPdfs = [];
        /** @var GuzzleRequest $request */
        foreach ($requests as $orderId => $request) {
            if (in_array($request, $failedRequests, true)) {
                $labelPdfs[$orderId] = null;
                continue;
            }
            $response = $request->getResponse();
            $labelPdfs[$orderId] = $response->getBody(true);
        }
        return $labelPdfs;
    }

    protected function getErrorsForFailedPdfs(array $labelPdfs): array
    {
        $errors = [];
        foreach ($labelPdfs as $orderId => $labelPdf) {
            if ($labelPdf !== null) {
                continue;
            }
            $errors[$orderId] = ['Label was created but there was a problem downloading the PDF'];
            $this->logNotice('Failed to download label PDF for Order %s', ['order' => $orderId], [static::LOG_CODE, 'DownloadPdfFail']);
        }
        return $errors;
    }

    protected function updateOrderLabels(OrderLabelCollection $orderLabels, array $labels, array $labelPdfs, array $errors)
    {
        $this->logDebug('Updating OrderLabels', [], [static::LOG_CODE, 'UpdateOrderLabels']);
        /** @var OrderLabel $orderLabel */
        foreach ($orderLabels as $orderLabel) {
            if (isset($errors[$orderLabel->getOrderId()])) {
                // Remove failed labels so the user can try again
                $this->removeFailedOrderLabel($orderLabel, $errors[$orderLabel->getOrderId()]);
                continue;
            }
            $labelResponse = $labels[$orderLabel->getOrderId()];
            $labelPdf = $labelPdfs[$orderLabel->getOrderId()];
            $this->updateAndSaveOrderLabel($orderLabel, $labelResponse, $labelPdf);
        }
    }

    protected function updateAndSaveOrderLabel(OrderLabel $orderLabel, LabelResponse $labelResponse, string $labelPdf, int $attempt = 1)
    {
        $this->logDebug('Updating OrderLabel %d (Order %s), attempt %d', [$orderLabel->getId(), $orderLabel->getOrderId(), $attempt], [static::LOG_CODE, 'UpdateOrderLabel']);

        try {
            $this->updateOrderLabel($orderLabel, $labelResponse, $labelPdf);
            $this->orderLabelService->save($orderLabel);
        } catch (NotModified $e) {
            // No-op
        } catch (Conflict $e) {
            if ($attempt == static::LABEL_SAVE_MAX_ATTEMPTS) {
                throw $e;
            }
            $fetchedLabel = $this->orderLabelService->fetch($orderLabel->getId());
            $this->updateAndSaveOrderLabel($fetchedLabel, $labelResponse, $labelPdf, ++$attempt);
        }
    }

    protected function updateOrderLabel(OrderLabel $orderLabel, LabelResponse $labelResponse, string $labelPdf)
    {
        $date = new StdlibDateTime();
        $orderLabel->setExternalId($labelResponse->getLabelId())
            ->setLabel(base64_encode($labelPdf))
            ->setStatus(OrderLabelStatus::NOT_PRINTED)
            ->setCreated($date->stdFormat());
    }

    protected function removeFailedOrderLabel(OrderLabel $orderLabel, array $errorMsgs): void
    {
        $this->logNotice('Failed to generate label for Order %s, reason(s): %s', [$orderLabel->getOrderId(), str_replace('%', '%%', implode('; ', $errorMsgs))], [static::LOG_CODE, 'Fail']);
        $this->orderLabelService->remove($orderLabel);
    }

    /**
     * @return array ['{orderId}' => bool || CG\Stdlib\Exception\Runtime\ValidationMessagesException]
     *          for each order whether a label was successfully created or a ValidationMessagesException if it errored
     */
    protected function buildResponseArray(OrderCollection $orders, array $errors): array
    {
        $response = [];
        foreach ($orders as $order) {
            if (!isset($errors[$order->getId()])) {
                $response[$order->getId()] = true;
                continue;
            }
            $validationException = new ValidationMessagesException('Validation error');
            $validationException->addErrors($errors[$order->getId()]);
            $response[$order->getId()] = $validationException;
        }
        return $response;
    }
}