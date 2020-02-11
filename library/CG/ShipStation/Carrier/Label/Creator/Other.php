<?php
namespace CG\ShipStation\Carrier\Label\Creator;

use CG\Account\Shared\Entity as Account;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Client\Gearman\Proxy\OrderLabelPdfToPng as OrderLabelPdfToPngProxy;
use CG\Order\Client\Gearman\WorkerFunction\OrderLabelPdfToPng as OrderLabelPdfToPngGF;
use CG\Order\Client\Gearman\Workload\OrderLabelPdfToPng as OrderLabelPdfToPngWorkload;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\ShippableInterface as Order;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\Tracking\Entity as OrderTracking;
use CG\Order\Shared\Tracking\Mapper as OrderTrackingMapper;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Carrier\Label\Canceller\Factory as LabelCancellerFactory;
use CG\ShipStation\Carrier\Label\CancellerInterface;
use CG\ShipStation\Carrier\Label\CreatorInterface;
use CG\ShipStation\Client as ShipStationClient;
use CG\ShipStation\Messages\Exception\InvalidStateException;
use CG\ShipStation\Request\Shipping\Label as LabelRequest;
use CG\ShipStation\Request\Shipping\Shipments\Mapper as ShipmentsRequestMapper;
use CG\ShipStation\Response\Shipping\Label as LabelResponse;
use CG\ShipStation\Response\Shipping\Label;
use CG\ShipStation\Response\Shipping\Shipment;
use CG\ShipStation\Response\Shipping\Shipments as ShipmentsResponse;
use CG\ShipStation\ShippingService\Factory as ShippingServiceFactory;
use CG\ShipStation\ShippingService\RequiresSignatureInterface;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use function CG\Stdlib\mergePdfData;
use function CG\Stdlib\collection_chunk;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\User\Entity as User;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\MultiTransferException;
use Guzzle\Http\Message\Request as GuzzleRequest;

class Other implements CreatorInterface, LoggerAwareInterface
{
    use LogTrait;

    const LABEL_FORMAT = 'pdf';
    const LABEL_SAVE_MAX_ATTEMPTS = 2;
    const PDF_DOWNLOAD_MAX_ATTEMPTS = 2;
    const ORDER_BATCH_SIZE = 100;
    const LOG_CODE = 'ShipStationLabelCreator';

    /** @var ShipStationClient */
    protected $shipStationClient;
    /** @var GuzzleClient */
    protected $guzzleClient;
    /** @var OrderLabelService */
    protected $orderLabelService;
    /** @var OrderTrackingMapper */
    protected $orderTrackingMapper;
    /** @var OrderTrackingService */
    protected $orderTrackingService;
    /** @var ShipmentsRequestMapper */
    protected $shipmentsRequestMapper;
    /** @var ShippingServiceFactory */
    protected $shippingServiceFactory;
    /** @var OrderLabelPdfToPngProxy */
    protected $orderLabelPdfToPng;
    /** @var LabelCancellerFactory */
    protected $labelCancellerFactory;

    /** @var CancellerInterface[] */
    protected $accountLabelCancellers = [];
    protected $testLabelWhitelist = ['usps-ss'];

    public function __construct(
        ShipStationClient $shipStationClient,
        GuzzleClient $guzzleClient,
        OrderLabelService $orderLabelService,
        OrderTrackingMapper $orderTrackingMapper,
        OrderTrackingService $orderTrackingService,
        ShipmentsRequestMapper $shipmentsRequestMapper,
        ShippingServiceFactory $shippingServiceFactory,
        OrderLabelPdfToPngProxy $orderLabelPdfToPng,
        LabelCancellerFactory $labelCancellerFactory
    ) {
        $this->shipStationClient = $shipStationClient;
        $this->guzzleClient = $guzzleClient;
        $this->orderLabelService = $orderLabelService;
        $this->orderTrackingMapper = $orderTrackingMapper;
        $this->orderTrackingService = $orderTrackingService;
        $this->shipmentsRequestMapper = $shipmentsRequestMapper;
        $this->shippingServiceFactory = $shippingServiceFactory;
        $this->orderLabelPdfToPng = $orderLabelPdfToPng;
        $this->labelCancellerFactory = $labelCancellerFactory;
    }

    public function createLabelsForOrders(
        OrderCollection $orders,
        OrderLabelCollection $orderLabels,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $orderParcelsData,
        OrganisationUnit $rootOu,
        User $user,
        Account $shippingAccount,
        Account $shipStationAccount
    ): array {
        $this->addGlobalLogEventParams(['ou' => $shippingAccount->getOrganisationUnitId(), 'rootOu' => $rootOu->getId(), 'account' => $shippingAccount->getId()]);
        $this->logInfo('Create labels request for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'Start']);
        $this->injectSignatureRequiredData($ordersData, $shippingAccount);

        $shipmentBatches = [];
        foreach (collection_chunk($orders, static::ORDER_BATCH_SIZE) as $orderBatch) {
            $shipments = $this->createShipmentsForOrders($orderBatch, $ordersData, $orderParcelsData, $shipStationAccount, $shippingAccount, $rootOu);
            $shipmentBatches[] = $shipments;
        }

        $shipmentErrors = $this->getErrorsForFailedShipments($shipmentBatches);
        $labels = $this->createLabelsForSuccessfulShipments($shipmentBatches, $shipStationAccount, $shippingAccount);
        $this->saveTrackingNumbersForSuccessfulLabels($labels, $orders, $user, $shippingAccount);
        $labelErrors = $this->getErrorsForUnsuccessfulLabels($labels);
        $labelPdfs = $this->downloadPdfsForLabels($labels);
        $pdfErrors = $this->getErrorsForFailedPdfs($labelPdfs);
        $this->cancelLabelsForFailedPdfs($labelPdfs, $labels, $orderLabels, $orders, $shipStationAccount, $shippingAccount);
        $errors = array_merge($shipmentErrors, $labelErrors, $pdfErrors);
        $this->updateOrderLabels($orderLabels, $labels, $labelPdfs, $errors);

        $this->logInfo('Labels created for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'End']);
        $this->removeGlobalLogEventParams(['ou', 'rootOu', 'account']);

        return $this->buildResponseArray($orders, $errors);
    }

    protected function injectSignatureRequiredData(OrderDataCollection $ordersData, Account $shippingAccount): void
    {
        $shippingService = ($this->shippingServiceFactory)($shippingAccount);
        if (!$shippingService instanceof RequiresSignatureInterface) {
            return;
        }
        /** @var OrderData $orderData */
        foreach ($ordersData as $orderData) {
            if ($shippingService->doesServiceRequireSignature($orderData->getService())) {
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
            $request = $this->shipmentsRequestMapper->createFromOrdersAndData(
                $orders, $ordersData, $orderParcelsData, $shipStationAccount, $shippingAccount, $rootOu
            );
            return $this->shipStationClient->sendRequest($request, $shipStationAccount);
        } catch (InvalidStateException $e) {
            $this->logWarningException($e, $e->getMessage(), [], [static::LOG_CODE, 'InvalidUSState']);
            throw $e;
        }
    }

    protected function getErrorsForFailedShipments(array $shipmentBatches): array
    {
        $errors = [];
        foreach ($shipmentBatches as $shipmentBatch) {
            /** @var Shipment $shipment */
            foreach ($shipmentBatch as $shipment) {
                if (empty($shipment->getErrors())) {
                    continue;
                }
                $errors[$shipment->getOrderId()] = $shipment->getErrors();
                $this->logNotice('Failed to create shipment for Order %s', ['order' => $shipment->getOrderId()], [static::LOG_CODE, 'ShipmentFail']);
            }
        }
        return $errors;
    }

    protected function createLabelsForSuccessfulShipments(
        array $shipmentBatches,
        Account $shipStationAccount,
        Account $shippingAccount
    ): array {
        $this->logDebug('Requesting labels for shipments', [], [static::LOG_CODE, 'Labels']);
        $labels = [];
        /** @var Shipment $shipment */
        foreach ($shipmentBatches as $shipmentBatch) {
            foreach ($shipmentBatch as $shipment) {
                if (!empty($shipment->getErrors())) {
                    continue;
                }
                try {
                    $request = new LabelRequest(
                        $shipment->getShipmentId(),
                        static::LABEL_FORMAT,
                        $this->isTestLabel($shippingAccount)
                    );
                    $labels[$shipment->getOrderId()] = $this->shipStationClient->sendRequest(
                        $request,
                        $shipStationAccount
                    );
                } catch (StorageException $e) {
                    $labels[$shipment->getOrderId()] = $this->convertStorageExceptionToLabelResponse($e);
                }
            }
        }
        return $labels;
    }

    protected function convertStorageExceptionToLabelResponse(StorageException $e): LabelResponse
    {
        if ($e->getPrevious() instanceof BadResponseException) {
            $json = $e->getPrevious()->getResponse()->getBody();
        } else {
            $json = json_encode(['errors' => ['message' => 'There was an unknown problem creating the label']]);
        }
        return LabelResponse::createFromJson($json);
    }

    protected function saveTrackingNumbersForSuccessfulLabels(
        array $labelResponses,
        OrderCollection $orders,
        User $user,
        Account $shippingAccount
    ): void {
        /** @var LabelResponse $labelResponse */
        foreach ($labelResponses as $orderId => $labelResponse) {
            if (!empty($labelResponse->getErrors())) {
                continue;
            }

            $trackingNumber = $this->getTrackingNumberOrCarrierReferenceNumber($labelResponse);
            if (!isset($trackingNumber)) {
                continue;
            }

            /** @var Order $order */
            $order = $orders->getById($orderId);
            foreach ($order->getChannelUpdatableOrders() as $updatableOrder) {
                $tracking = $this->mapOrderTrackingFromLabelResponse($labelResponse, $updatableOrder, $user, $shippingAccount, $trackingNumber);
                $this->orderTrackingService->save($tracking);
                $this->orderTrackingService->createGearmanJob($updatableOrder);
            }
        }
    }

    protected function mapOrderTrackingFromLabelResponse(
        LabelResponse $labelResponse,
        Order $order,
        User $user,
        Account $shippingAccount,
        string $trackingNumber
    ): OrderTracking {
        return $this->orderTrackingMapper->fromArray([
            'organisationUnitId' => $order->getOrganisationUnitId(),
            'orderId' => $order->getId(),
            'userId' => $user->getId(),
            'timestamp' => (new StdlibDateTime())->stdFormat(),
            'carrier' => $shippingAccount->getDisplayableChannel(),
            'number' => $trackingNumber,
        ]);
    }

    protected function isTestLabel(Account $shippingAccount): bool
    {
        if (ENVIRONMENT == 'live') {
            return false;
        }
        if (in_array($shippingAccount->getChannel(), $this->testLabelWhitelist)) {
            return true;
        }
        return false;
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
        $this->logDebug('Downloading PDFs for labels', [], [static::LOG_CODE, 'LabelPdfs']);
        $labelRequests = [];
        $formRequests = [];
        /** @var LabelResponse $label */
        foreach ($labels as $orderId => $label) {
            if (!empty($label->getErrors())) {
                continue;
            }
            $labelRequests[$orderId] = $this->guzzleClient->get($label->getLabelDownload()->getHref());
            if ($label->getFormDownload() && $label->getFormDownload()->getHref()) {
                $formRequests[$orderId] = $this->guzzleClient->get($label->getFormDownload()->getHref());
            }
        }

        $labelPdfs = $this->sendPdfDownloadRequests($labelRequests);
        if (empty($formRequests)) {
            return $labelPdfs;
        }
        $this->logDebug('Downloading PDFs for customs forms', [], [static::LOG_CODE, 'FormPdfs']);
        $formPdfs = $this->sendPdfDownloadRequests($formRequests);
        return $this->mergeFormPdfsWithLabelPdfs($formPdfs, $labelPdfs);
    }

    protected function sendPdfDownloadRequests(array $requests, $attempt = 1): array
    {
        try {
            $this->guzzleClient->send($requests);
            return $this->getPdfsFromRequests($requests);
        } catch (MultiTransferException $e) {
            $this->logWarningException($e, 'Failed to download one or more labels from ShipStation at attempt %d', [$attempt], [static::LOG_CODE, 'LabelPdfs', 'Error']);
            $labelPdfs = $this->getPdfsFromRequests($requests, $e->getFailedRequests());
            if ($attempt < static::PDF_DOWNLOAD_MAX_ATTEMPTS) {
                $requestsRetry = $this->getPdfDownloadRequestsToRetry($requests, $e->getFailedRequests());
                $labelPdfsRetry = $this->sendPdfDownloadRequests($requestsRetry, ++$attempt);
                $labelPdfs = array_merge($labelPdfs, $labelPdfsRetry);
            }
            return $labelPdfs;
        }
    }

    protected function getPdfsFromRequests(array $requests, array $failedRequests = []): array
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

    protected function getPdfDownloadRequestsToRetry(array $requests, array $failedRequests): array
    {
        $requestsToRetry = [];
        /** @var GuzzleRequest $request */
        foreach ($requests as $orderId => $request) {
            if (in_array($request, $failedRequests, true)) {
                // We can't use the $failedRequests directly as they're not keyed by $orderId
                $requestsToRetry[$orderId] = $request;
            }
        }
        return $requestsToRetry;
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

    protected function mergeFormPdfsWithLabelPdfs(array $formPdfs, array $labelPdfs): array
    {
        $mergedPdfs = [];
        foreach ($labelPdfs as $orderId => $labelPdf) {
            if (!isset($formPdfs[$orderId])) {
                $mergedPdfs[$orderId] = $labelPdf;
                continue;
            }
            $formPdf = $formPdfs[$orderId];
            $mergedPdfs[$orderId] = mergePdfData([$labelPdf, $formPdf]);
        }
        return $mergedPdfs;
    }

    protected function cancelLabelsForFailedPdfs(
        array $labelPdfs,
        array $labels,
        OrderLabelCollection $orderLabels,
        OrderCollection $orders,
        Account $shipStationAccount,
        Account $shippingAccount
    ): void {
        $orderLabelsToCancel = $this->getOrderLabelsForFailedPdfs($labelPdfs, $labels, $orderLabels);
        if ($orderLabelsToCancel->count() == 0) {
            return;
        }
        $labelCanceller = $this->getLabelCanceller($shippingAccount);
        try {
            $labelCanceller->cancelOrderLabels($orderLabelsToCancel, $orders, $shippingAccount, $shipStationAccount);
        } catch (StorageException $e) {
            $this->logWarningException($e, 'Failed to cancel one or more labels wth ShipStation that we couldnt download the PDF for, ignoring', [], [static::LOG_CODE, 'LabelPdfs', 'Cancel', 'Error']);
            // If we can't cancel the labels just carry on anyway as we don't want to stop the user retrying
        }
    }

    protected function getOrderLabelsForFailedPdfs(
        array $labelPdfs,
        array $labels,
        OrderLabelCollection $orderLabels
    ): OrderLabelCollection {
        $orderLabelsToCancel = new OrderLabelCollection(OrderLabel::class, __FUNCTION__);
        foreach ($labelPdfs as $orderId => $labelPdf) {
            if ($labelPdf !== null) {
                continue;
            }
            /** @var LabelResponse $labelResponse */
            $labelResponse = $labels[$orderId];
            /** @var OrderLabel $orderLabel */
            $orderLabel = $orderLabels->getBy('orderId', $orderId)->getFirst();
            // Need to ensure the externalId is set as its required for cancelling
            $orderLabel->setExternalId($labelResponse->getLabelId());
            $orderLabelsToCancel->attach($orderLabel);
        }
        return $orderLabelsToCancel;
    }

    protected function getLabelCanceller(Account $shippingAccount): CancellerInterface
    {
        if (isset($this->accountLabelCancellers[$shippingAccount->getChannel()])) {
            return $this->accountLabelCancellers[$shippingAccount->getChannel()];
        }
        $this->accountLabelCancellers[$shippingAccount->getChannel()] = ($this->labelCancellerFactory)($shippingAccount->getChannel());
        return $this->accountLabelCancellers[$shippingAccount->getChannel()];
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
            $this->createJobToConvertLabelPdfToPng($orderLabel);
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
        if ($orderLabel->getId()) {
            $this->orderLabelService->remove($orderLabel);
        }
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
            foreach ($errors[$order->getId()] as $error) {
                $validationException->addErrorWithField($order->getId().':Error', $error);
            }
            $response[$order->getId()] = $validationException;
        }
        return $response;
    }

    protected function createJobToConvertLabelPdfToPng(OrderLabel $orderLabel): Other
    {
        $workload = new OrderLabelPdfToPngWorkload($orderLabel->getId());
        $unique = OrderLabelPdfToPngGF::FUNCTION_NAME . '-OrderLabel' . $orderLabel->getId();
        $this->orderLabelPdfToPng->proxyBackground(OrderLabelPdfToPngGF::FUNCTION_NAME, serialize($workload), $unique);
        return $this;
    }

    protected function getTrackingNumberOrCarrierReferenceNumber(LabelResponse $labelResponse): ?string
    {
        if ($labelResponse->getTrackingNumber() !== "") {
            return $labelResponse->getTrackingNumber();
        }
        if ($labelResponse->getCarrierReferenceNumber() !== "") {
            return $labelResponse->getCarrierReferenceNumber();
        }
        return null;
    }
}