<?php
namespace CG\ShipStation\Carrier\Label\Canceller;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\ShipStation\Client as ShipStationClient;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;

class Usps extends Other
{
    /** @var ShippingLedgerService */
    protected $shippingLedgerService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    public function __construct(ShipStationClient $shipStationClient, ShippingLedgerService $shippingLedgerService, OrganisationUnitService $organisationUnitService)
    {
        parent::__construct($shipStationClient);
        $this->shippingLedgerService = $shippingLedgerService;
        $this->organisationUnitService = $organisationUnitService;
    }

    protected function handleSuccess(OrderLabel $orderLabel, Account $shippingAccount): void
    {
        $shippingLedger = $this->shippingLedgerService->fetch($shippingAccount->getRootOrganisationUnitId());
        $organisationUnit = $this->organisationUnitService->fetch($shippingAccount->getRootOrganisationUnitId());
        $this->shippingLedgerService->credit($shippingLedger, $organisationUnit, $orderLabel->getCostPrice());
    }
}