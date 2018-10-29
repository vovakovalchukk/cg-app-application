<?php
namespace ShipStation\Setup;

use CG\Account\Shared\Entity as Account;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use CG\Channel\Type as ChannelType;
use CG\Clearbooks\Customer\Service as ClearBooksService;
use CG\Http\Exception\Exception4xx\NotFound;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\ShipStation\Account\CreationService as AccountCreationService;
use CG\Channel\Shipping\Provider\Carrier\Entity as Carrier;
use CG\ShipStation\Credentials;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use ShipStation\SetupInterface;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\View\Model\ViewModel;
use CG\Billing\Shipping\Ledger\Entity as ShippingLedger;
use CG\Clearbooks\Customer\Entity as ClearBooksCustomer;

class Usps implements SetupInterface
{
    const CLEAR_BOOKS_CUSTOMER_SUFFIX = 'shipping';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var Redirect */
    protected $redirectHelper;
    /** @var AccountCreationService */
    protected $accountCreationService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var ClearBooksService */
    protected $clearBooksService;
    /** @var ShippingLedgerService */
    protected $shippingLedgerService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        Redirect $redirectHelper,
        AccountCreationService $accountCreationService,
        ClearBooksService $clearBooksService,
        OrganisationUnitService $organisationUnitService,
        ShippingLedgerService $shippingLedgerService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->redirectHelper = $redirectHelper;
        // This is a specially configured version for USPS, see ShipStation/config/module.config.php
        $this->accountCreationService = $accountCreationService;
        $this->organisationUnitService = $organisationUnitService;
        $this->clearBooksService = $clearBooksService;
        $this->shippingLedgerService = $shippingLedgerService;
    }


    public function __invoke(
        Carrier $carrier,
        int $organisationUnitId,
        Account $account = null,
        Credentials $credentials = null
    ): ViewModel {
        $savedAccount = $this->accountCreationService->connectAccount(
            $organisationUnitId,
            $account ? $account->getId() : null,
            ['channel' => $carrier->getChannelName()]
        );

        $clearBooksCustomer = $this->createClearBooksAccount($organisationUnitId);
        $this->addClearBooksAccountToShippingLedger($clearBooksCustomer, $organisationUnitId);

        $this->redirectHelper->toRoute($this->getAccountRoute(), ['account' => $savedAccount->getId(), 'type' => ChannelType::SHIPPING]);
        return $this->viewModelFactory->newInstance();
    }

    protected function getAccountRoute(): string
    {
        return implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
    }

    protected function createClearBooksAccount(int $organisationUnitId): ClearBooksCustomer
    {
        $clearBooksCustomer = $this->clearBooksService->saveCustomer($this->organisationUnitService->fetch($organisationUnitId), static::CLEAR_BOOKS_CUSTOMER_SUFFIX);
        return $this->clearBooksService->getCustomers([$clearBooksCustomer->getId()])[0];
    }

    protected function addClearBooksAccountToShippingLedger(ClearBooksCustomer $clearBooksCustomer, int $organisationUnitId)
    {
        $ledgerEntry = $this->shippingLedgerService->fetch($organisationUnitId);
        $ledgerEntry
            ->setClearbooksCustomerId($clearBooksCustomer->getId())
            ->setClearbooksStatementUrl($clearBooksCustomer->getStatementUrl());
        $this->shippingLedgerService->save($ledgerEntry);
    }
}