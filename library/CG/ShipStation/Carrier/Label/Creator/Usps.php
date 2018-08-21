<?php
namespace CG\ShipStation\Carrier\Label\Creator;

use CG\Account\Shared\Entity as Account;
use CG\Billing\Shipping\Charge\Entity as ShippingCharge;
use CG\Billing\Shipping\Ledger\Entity as ShippingLedger;
use CG\Billing\Shipping\Ledger\Exception\InsufficientBalanceException;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData as OrderData;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Tracking\Mapper as OrderTrackingMapper;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Client as ShipStationClient;
use CG\ShipStation\Request\Shipping\Label\Rate as RateLabelRequest;
use CG\ShipStation\Response\Shipping\Label as LabelResponse;
use CG\User\Entity as User;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\BadResponseException;
use Throwable;

class Usps extends Other
{
    const LOG_CODE = 'ShipStationUspsLabelCreator';

    /** @var ShippingLedgerService */
    protected $shippingLedgerService;
    
    public function __construct(
        ShipStationClient $shipStationClient,
        GuzzleClient $guzzleClient,
        OrderLabelService $orderLabelService,
        OrderTrackingMapper $orderTrackingMapper,
        OrderTrackingService $orderTrackingService,
        ShippingLedgerService $shippingLedgerService
    ) {
        parent::__construct($shipStationClient, $guzzleClient, $orderLabelService, $orderTrackingMapper, $orderTrackingService);
        $this->shippingLedgerService = $shippingLedgerService;
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
        $this->logInfo('Create USPS labels request for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'Start']);

        $shippingLedger = $this->shippingLedgerService->fetch($rootOu->getId());
        $this->shippingLedgerService->debit($shippingLedger, $rootOu, $ordersData->getTotalCost());
        $this->setCostOnOrderLabels($orderLabels, $ordersData);

        $labelResults = $this->createLabelsFromRates($ordersData, $shippingAccount, $shipStationAccount);
        $this->saveTrackingNumbersForSuccessfulLabels($labelResults->getResponses(), $orders, $user, $shippingAccount);
        $labelExceptions = $this->getErrorsForFailedLabels($labelResults->getThrowables());
        $labelErrors = $this->getErrorsForUnsuccessfulLabels($labelResults->getResponses());
        // Note: we're deliberately NOT refunding the users balance for any failures as there's
        // no way for us to know if we've been charged by Stamps.com or not.
        // Most user errors SHOULD be caught at the fetch-rates stage.

        $labelPdfs = $this->downloadPdfsForLabels($labelResults->getResponses());
        $pdfErrors = $this->getErrorsForFailedPdfs($labelPdfs);
        $errors = array_merge($labelExceptions, $labelErrors, $pdfErrors);
        $this->updateOrderLabels($orderLabels, $labelResults->getResponses(), $labelPdfs, $errors);

        $this->logInfo('Labels created for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'End']);
        $this->removeGlobalLogEventParams(['ou', 'rootOu', 'account']);

        return $this->buildResponseArray($orders, $errors);
    }

    protected function hasSufficientBalance(ShippingLedger $shippingLedger, OrderDataCollection $ordersData): bool
    {
        return $shippingLedger->getBalance() >= $ordersData->getTotalCost();
    }

    protected function setCostOnOrderLabels(OrderLabelCollection $orderLabels, OrderDataCollection $ordersData): void
    {
        /** @var OrderLabel $orderLabel */
        foreach ($orderLabels as $orderLabel) {
            $orderData = $ordersData->getById($orderLabel->getOrderId());
            $orderLabel->setCostPrice($orderData->getCost());
            $orderLabel->setCostCurrencyCode(ShippingCharge::VALUE_CURRENCY);
        }
    }

    protected function createLabelsFromRates(
        OrderDataCollection $ordersData,
        Account $shippingAccount,
        Account $shipStationAccount
    ): LabelResults {
        $this->logDebug('Requesting labels from rates', [], [static::LOG_CODE, 'Labels']);
        $labelResults = new LabelResults();
        /** @var OrderData $orderData */
        foreach ($ordersData as $orderData) {
            try {
                $request = new RateLabelRequest($orderData->getService(), static::LABEL_FORMAT, $this->isTestLabel($shippingAccount));
                $labelResults->addResponse($orderData->getId(), $this->shipStationClient->sendRequest($request, $shipStationAccount));
            } catch (Throwable $throwable) {
                $this->logCriticalException($throwable, 'Problem creating label from rate, we dont know if money was used or not.', [], [static::LOG_CODE, 'Failure']);
                $labelResults->addThrowable($orderData->getId(), $throwable);
            }
        }
        return $labelResults;
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