<?php
namespace CG\ShipStation\Carrier\Label\Creator;

use CG\Account\Shared\Entity as Account;
use CG\Billing\Shipping\Ledger\Entity as ShippingLedger;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Client as ShipStationClient;
use Guzzle\Http\Client as GuzzleClient;

class Usps extends Other
{
    const LOG_CODE = 'ShipStationUspsLabelCreator';

    /** @var ShippingLedgerService */
    protected $shippingLedgerService;
    
    /** @var ShippingLedger */
    protected $shippingLedger;
    
    public function __construct(
        ShipStationClient $shipStationClient,
        GuzzleClient $guzzleClient,
        OrderLabelService $orderLabelService,
        ShippingLedgerService $shippingLedgerService
    ) {
        parent::__construct($shipStationClient, $guzzleClient, $orderLabelService);
        $this->shippingLedgerService = $shippingLedgerService;
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
        $this->logInfo('Create USPS labels request for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'Start']);

        if (!$this->hasSufficientBalance($rootOu)) {
            if (!$this->isAutoTopUpEnabled()) {
                throw new InsufficientBalanceException();
            }
            $this->topUpShippingLedgerByRequiredIncrement();
        }

        $shipments = $this->createShipmentsForOrders($orders, $ordersData, $orderParcelsData, $shipStationAccount, $shippingAccount, $rootOu);
        $shipmentErrors = $this->getErrorsForFailedShipments($shipments);
        $labels = $this->createLabelsForSuccessfulShipments($shipments, $shipStationAccount, $shippingAccount);
        $labelErrors = $this->getErrorsForFailedLabels($labels, $shipments);
        $labelPdfs = $this->downloadPdfsForLabels($labels);
        $pdfErrors = $this->getErrorsForFailedPdfs($labelPdfs);
        $errors = array_merge($shipmentErrors, $labelErrors, $pdfErrors);
        $this->updateOrderLabels($orderLabels, $labels, $labelPdfs, $errors);

        $this->logInfo('Labels created for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'End']);
        $this->removeGlobalLogEventParams(['ou', 'rootOu', 'account']);

        return $this->buildResponseArray($orders, $errors);
    }

    protected function hasSufficientBalance(OrganisationUnit $rootOu): bool
    {
        $shippingLedger = $this->fetchShippingLedgerForOu($rootOu);
        // TODO: get total cost
    }
    
    protected function fetchShippingLedgerForOu(OrganisationUnit $rootOu): ShippingLedger
    {
        if ($this->shippingLedger) {
            return $this->shippingLedger;
        }
        $this->shippingLedger = $this->shippingLedgerService->fetch($rootOu->getId());
        return $this->shippingLedger;
    }
}