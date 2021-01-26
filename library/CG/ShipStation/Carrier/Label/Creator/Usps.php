<?php
namespace CG\ShipStation\Carrier\Label\Creator;

use CG\Account\Shared\Entity as Account;
use CG\Billing\Shipping\Charge\Entity as ShippingCharge;
use CG\Billing\Shipping\Ledger\Entity as ShippingLedger;
use CG\Billing\Shipping\Ledger\Exception\InsufficientBalanceException;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use CG\Order\Client\Gearman\Proxy\OrderLabelPdfToPng as OrderLabelPdfToPngProxy;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData as OrderData;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Tracking\Mapper as OrderTrackingMapper;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Carrier\Label\Canceller\Factory as LabelCancellerFactory;
use CG\ShipStation\Client as ShipStationClient;
use CG\ShipStation\Request\Shipping\Label\Rate as RateLabelRequest;
use CG\ShipStation\Request\Shipping\Label\Query as QueryLabelRequest;
use CG\ShipStation\Response\Shipping\Label as LabelResponse;
use CG\ShipStation\Response\Shipping\Label\Query as QueryLabelResponse;
use CG\ShipStation\Request\Shipping\Shipments\Mapper as ShipmentsRequestMapper;
use CG\ShipStation\ShippingService\Factory as ShippingServiceFactory;
use CG\User\Entity as User;
use DateTime;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\BadResponseException;
use Throwable;
use CG\ShipStation\Carrier\Rates\Usps\ShipmentIdStorage;

class Usps extends Other
{
    const LOG_CODE = 'ShipStationUspsLabelCreator';

    /** @var ShippingLedgerService */
    protected $shippingLedgerService;
    /** @var ShipmentIdStorage */
    protected $shipmentIdStorage;

    /** @var ShippingLedger */
    protected $shippingLedger;

    public function __construct(
        ShipStationClient $shipStationClient,
        GuzzleClient $guzzleClient,
        OrderLabelService $orderLabelService,
        OrderTrackingMapper $orderTrackingMapper,
        OrderTrackingService $orderTrackingService,
        ShipmentsRequestMapper $shipmentsRequestMapper,
        ShippingServiceFactory $shippingServiceFactory,
        OrderLabelPdfToPngProxy $orderLabelPdfToPng,
        LabelCancellerFactory $labelCancellerFactory,
        ShippingLedgerService $shippingLedgerService,
        ShipmentIdStorage $shipmentIdStorage
    ) {
        parent::__construct(
            $shipStationClient,
            $guzzleClient,
            $orderLabelService,
            $orderTrackingMapper,
            $orderTrackingService,
            $shipmentsRequestMapper,
            $shippingServiceFactory,
            $orderLabelPdfToPng,
            $labelCancellerFactory
        );
        $this->shippingLedgerService = $shippingLedgerService;
        $this->shipmentIdStorage = $shipmentIdStorage;
    }

    public function createLabelsForOrders(
        OrderCollection $orders,
        OrderLabelCollection $orderLabels,
        OrderDataCollection $ordersData,
        OrderItemsDataCollection $orderItemsData,
        OrderParcelsDataCollection $orderParcelsData,
        OrganisationUnit $rootOu,
        User $user,
        Account $shippingAccount,
        Account $shipStationAccount
    ): array {
        $this->addGlobalLogEventParams(['ou' => $shippingAccount->getOrganisationUnitId(), 'rootOu' => $rootOu->getId(), 'account' => $shippingAccount->getId()]);
        $this->logInfo('Create USPS labels request for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'Start']);

        $labelResults = new LabelResults();

        $invalidOrdersData = $this->validateOrdersData($ordersData, $labelResults);
        $labelResults = $this->markInvalidOrdersAsFailed($invalidOrdersData, $labelResults);
        $this->removeInvalidOrdersDataAndLabels($invalidOrdersData, $ordersData, $orderLabels);
        // If all invalid
        if (count($ordersData) == 0) {
            $labelExceptions = $this->getErrorsForFailedLabels($labelResults->getThrowables());
            return $this->buildResponseArray($orders, $labelExceptions);
        }
        $this->debitLedgerForLabels($ordersData, $rootOu);
        $this->setCostOnOrderLabels($orderLabels, $ordersData);

        $startDateTime = new DateTime();
        $labelResults = $this->createLabelsFromRates($ordersData, $shippingAccount, $shipStationAccount, $labelResults);
        $labelResults = $this->checkIfFailedLabelsWereActuallyCreatedAndUpdateResults(
            $labelResults, $startDateTime, $ordersData, $shippingAccount, $shipStationAccount
        );
        $this->creditBackFailedLabels($labelResults, $ordersData, $rootOu);

        $this->saveTrackingNumbersForSuccessfulLabels($labelResults->getResponses(), $orders, $user, $shippingAccount);
        $labelExceptions = $this->getErrorsForFailedLabels($labelResults->getThrowables());
        $labelErrors = $this->getErrorsForUnsuccessfulLabels($labelResults->getResponses());

        $labelPdfs = $this->downloadPdfsForLabels($labelResults->getResponses());
        $pdfErrors = $this->getErrorsForFailedPdfs($labelPdfs);
        // We don't do any refunds for errors at this stage as it is after the labels have been created

        $errors = array_merge($labelExceptions, $labelErrors, $pdfErrors);
        $this->updateOrderLabels($orderLabels, $labelResults->getResponses(), $labelPdfs, $errors);

        $this->logInfo('Labels created for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'End']);
        $this->removeGlobalLogEventParams(['ou', 'rootOu', 'account']);

        return $this->buildResponseArray($orders, $errors);
    }

    protected function validateOrdersData(OrderDataCollection $ordersData): OrderDataCollection
    {
        $invalidOrders = new OrderDataCollection();
        /** @var OrderData $orderData */
        foreach ($ordersData as $orderData) {
            // Safety check: ensure a cost is set
            if ($orderData->getCost() !== null) {
                continue;
            }
            $this->logAlert('USPS label request received for Order %s with no cost associated with it, will drop', ['order' => $orderData->getId()], [static::LOG_CODE, 'CostMissing']);
            $invalidOrders->attach($orderData);
        }
        return $invalidOrders;
    }

    protected function markInvalidOrdersAsFailed(OrderDataCollection $invalidOrdersData, LabelResults $labelResults): LabelResults
    {
        /** @var OrderData $orderData */
        foreach ($invalidOrdersData as $orderData) {
            $labelResults->addThrowable($orderData->getId(), new \RuntimeException('No cost was found for this label'));
        }
        return $labelResults;
    }

    protected function removeInvalidOrdersDataAndLabels(
        OrderDataCollection $invalidOrdersData,
        OrderDataCollection $ordersData,
        OrderLabelCollection $orderLabels
    ): OrderDataCollection {
        $ordersData->removeAll($invalidOrdersData);
        /** @var OrderData $orderData */
        foreach ($invalidOrdersData as $orderData) {
            $orderLabel = $orderLabels->getBy('orderId', $orderData->getId())->getFirst();
            $this->orderLabelService->remove($orderLabel);
            $orderLabels->detach($orderLabel);
        }
        return $ordersData;
    }

    protected function debitLedgerForLabels(OrderDataCollection $ordersData, OrganisationUnit $rootOu): void
    {
        $shippingLedger = $this->fetchShippingLedgerForOu($rootOu);
        $this->shippingLedgerService->debit($shippingLedger, $rootOu, $ordersData->getTotalCost());
    }

    protected function fetchShippingLedgerForOu(OrganisationUnit $rootOu): ShippingLedger
    {
        if ($this->shippingLedger) {
            return $this->shippingLedger;
        }
        $this->shippingLedger = $this->shippingLedgerService->fetch($rootOu->getId());
        return $this->shippingLedger;
    }

    protected function setCostOnOrderLabels(OrderLabelCollection $orderLabels, OrderDataCollection $ordersData): void
    {
        /** @var OrderLabel $orderLabel */
        foreach ($orderLabels as $orderLabel) {
            /** @var OrderData $orderData */
            $orderData = $ordersData->getById($orderLabel->getOrderId());
            if (!$orderData) {
                continue;
            }
            $orderLabel->setCostPrice($orderData->getCost());
            $orderLabel->setCostCurrencyCode(ShippingCharge::VALUE_CURRENCY);
        }
    }

    protected function createLabelsFromRates(
        OrderDataCollection $ordersData,
        Account $shippingAccount,
        Account $shipStationAccount,
        LabelResults $labelResults
    ): LabelResults {
        $this->logDebug('Requesting labels from rates', [], [static::LOG_CODE, 'Labels']);
        /** @var OrderData $orderData */
        foreach ($ordersData as $orderData) {
            try {
                $request = new RateLabelRequest($orderData->getService(), static::LABEL_FORMAT, $this->isTestLabel($shippingAccount));
                $labelResults->addResponse($orderData->getId(), $this->shipStationClient->sendRequest($request, $shipStationAccount));
            } catch (Throwable $throwable) {
                $this->logWarningException($throwable, 'Problem creating label from rate, we don\'t know if money was used or not.', [], [static::LOG_CODE, 'Failure']);
                $labelResults->addThrowable($orderData->getId(), $throwable);
            }
        }
        return $labelResults;
    }

    /* Because we get charged by Stamps.com for any created labels we need to make sure any marked as
     * failed were definitely not created before refunding them */
    protected function checkIfFailedLabelsWereActuallyCreatedAndUpdateResults(
        LabelResults $labelResults,
        DateTime $startDateTime,
        OrderDataCollection $ordersData,
        Account $shippingAccount,
        Account $shipStationAccount
    ): LabelResults {
        if (!$this->didAnyLabelsFailToCreate($labelResults)) {
            return $labelResults;
        }
        $this->logNotice('Some labels seemingly failed to create, will double-check by attempting to fetch them', [], [static::LOG_CODE, 'Failure', 'Check']);
        $fetchedLabels = $this->fetchLabelsCreatedSince($startDateTime, $shippingAccount, $shipStationAccount);
        $activeLabels = $this->filterQueryLabelsResponseToActiveLabelsByShipmentId($fetchedLabels, $ordersData);
        if (empty($activeLabels)) {
            return $labelResults;
        }
        return $this->updateLabelResultsWithFetchedLabels($labelResults, $activeLabels, $ordersData);
    }

    protected function didAnyLabelsFailToCreate(LabelResults $labelResults): bool
    {
        if (!empty($labelResults->getThrowables())) {
            return true;
        }
        foreach ($labelResults->getResponses() as $labelResponse) {
            if (!empty($labelResponse->getErrors())) {
                return true;
            }
        }
        return false;
    }

    /** @return LabelResponse[] */
    protected function fetchLabelsCreatedSince(
        DateTime $startDateTime,
        Account $shippingAccount,
        Account $shipStationAccount
    ): array {
        // Give ourselves a little overlap in case ShipEngines time is out of sync with ours
        $startDateTime->sub(new \DateInterval('P1M'));
        $page = 0;
        $labels = [];
        do {
            $response = $this->fetchPageOfLabelsCreatedSince(
                $startDateTime, $shippingAccount, $shipStationAccount, ++$page
            );
            $labels = array_merge($labels, $response->getLabels());
        } while ($response->getPages() > $page);
        return $labels;
    }

    protected function fetchPageOfLabelsCreatedSince(
        DateTime $startDateTime,
        Account $shippingAccount,
        Account $shipStationAccount,
        int $page
    ): QueryLabelResponse {
        $request = (new QueryLabelRequest())
            ->setCarrierId($shippingAccount->getExternalId())
            ->setCreatedAtStart($startDateTime)
            ->setPage($page)
            ->setPageSize(QueryLabelRequest::MAX_PAGE_SIZE);
        return $this->shipStationClient->sendRequest($request, $shipStationAccount);
    }

    /**
     * @return LabelResponse[]
     */
    protected function filterQueryLabelsResponseToActiveLabelsByShipmentId(array $labels): array
    {
        $activeLabels = [];
        /** @var LabelResponse $label */
        foreach ($labels as $label) {
            if (!in_array($label->getStatus(), LabelResponse::getActiveStatuses())) {
                continue;
            }
            $activeLabels[$label->getShipmentId()] = $label;
        }
        return $activeLabels;
    }

    protected function getStoredShipmentIdForOrderId(string $orderId): ?string
    {
        return $this->shipmentIdStorage->get($orderId);
    }

    protected function updateLabelResultsWithFetchedLabels(
        LabelResults $labelResults,
        array $activeLabels,
        OrderDataCollection $ordersData
    ): LabelResults {
        $updatedResults = new LabelResults();
        foreach ($labelResults->getThrowables() as $orderId => $throwable) {
            $storedShipmentId = $this->getStoredShipmentIdForOrderId($orderId);
            /** @var OrderData $orderData */
            $orderData = $ordersData->getById($orderId);
            if (!isset($activeLabels[$storedShipmentId])) {
                $updatedResults->addThrowable($orderId, $throwable);
                continue;
            }
            $updatedResults->addResponse($orderId, $activeLabels[$storedShipmentId]);
        }
        /** @var LabelResponse $response */
        foreach ($labelResults->getResponses() as $orderId => $response) {
            $storedShipmentId = $this->getStoredShipmentIdForOrderId($orderId);
            if (empty($response->getErrors())) {
                $updatedResults->addResponse($orderId, $response);
                continue;
            }
            if (!isset($activeLabels[$storedShipmentId])) {
                $updatedResults->addResponse($orderId, $response);
                continue;
            }
            $updatedResults->addResponse($orderId, $activeLabels[$storedShipmentId]);
        }
        return $updatedResults;
    }

    protected function creditBackFailedLabels(
        LabelResults $labelResults,
        OrderDataCollection $ordersData,
        OrganisationUnit $rootOu
    ): void {
        $failCount = 0;
        $amount = 0;
        foreach ($labelResults->getThrowables() as $orderId => $throwable) {
            /** @var OrderData $orderData */
            $orderData = $ordersData->getById($orderId);
            if (!$orderData) {
                continue;
            }
            $amount += $orderData->getCost();
            $failCount++;
            $this->logDebug('Failed to create label for Order %d, cost %.2f', [$orderId, $amount], [static::LOG_CODE, 'Failure', 'Order']);
        }
        /** @var LabelResponse $response */
        foreach ($labelResults->getResponses() as $orderId => $response) {
            if (empty($response->getErrors())) {
                continue;
            }
            /** @var OrderData $orderData */
            $orderData = $ordersData->getById($orderId);
            $amount += $orderData->getCost();
            $failCount++;
            $this->logDebug('Failed to create label for Order %d, cost %.2f', [$orderId, $amount], [static::LOG_CODE, 'Failure', 'Order']);
        }
        if ($amount == 0) {
            return;
        }

        $this->logDebug('%d labels failed to create, crediting the user a total of %.2f', [$failCount, $amount], [static::LOG_CODE, 'Failure', 'Credit']);
        $shippingLedger = $this->fetchShippingLedgerForOu($rootOu);
        $this->shippingLedgerService->credit($shippingLedger, $rootOu, $amount);
    }

    protected function getErrorsForFailedLabels(array $throwables): array
    {
        $errors = [];
        /** @var Throwable $throwable */
        foreach ($throwables as $orderId => $throwable) {
            $errors[$orderId] = [$this->parseErrorMessageFromThrowable($throwable)];
        }
        return $errors;
    }

    protected function parseErrorMessageFromThrowable(Throwable $throwable): string
    {
        $defaultError = 'There was an unknown problem generating a label for this order.';
        $previous = $throwable;
        while (!$previous instanceof BadResponseException && $previous->getPrevious()) {
            $previous = $previous->getPrevious();
        }
        if (!$previous instanceof BadResponseException) {
            return $defaultError;
        }
        try {
            $json = $previous->getResponse()->json();
        } catch (\Exception $e) {
            return $defaultError;
        }
        if (!isset($json['errors'], $json['errors'][0]['message'])) {
            return $defaultError;
        }
        return implode('; ', array_column($json['errors'], 'message'));
    }
}