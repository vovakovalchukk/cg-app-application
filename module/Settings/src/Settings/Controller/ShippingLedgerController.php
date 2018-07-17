<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\OrganisationUnit\Entity as OrganisatuonUnit;
use CG\Billing\Shipping\Ledger\Entity as ShippingLedger;
use CG\Billing\Shipping\Ledger\Exception\ShippingLedgerTopUpException;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use CG\Billing\Transaction\Entity as Transaction;
use CG\Billing\Transaction\Status as TransactionStatus;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Payment\Exception\FailedPaymentException;
use CG\Payment\OneOffPaymentService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Account\Client\Service as AccountService;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class ShippingLedgerController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CONSTANT = 'ShippingLedgerController';

    const ROUTE = 'Ledger';
    const ROUTE_TOPUP = 'Topup';
    const ROUTE_SAVE = 'Save';

    const DEFAULT_TOPUP_AMMOUNT = 100;
    const SHIPPING_CLEARBOOKS_ACCOUNT_CODE = '1002025';
    const USPS_INVOICE_DESCRIPTION = 'USPS Shipping';
    const USPS_ITEM_DESCRIPTION = 'USPS Shipping top-up';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ShippingLedgerService */
    protected $shippingLedgerService;
    /** @var AccountService */
    protected $accountService;
    /** @var OneOffPaymentService */
    protected $organisationUnitService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ShippingLedgerService $shippingLedgerService,
        AccountService $accountService,
        OrganisationUnitService $organisationUnitService
    )
    {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->shippingLedgerService = $shippingLedgerService;
        $this->accountService = $accountService;
        $this->organisationUnitService = $organisationUnitService;
    }

    public function topupAction()
    {
        $account = $this->getAccount($this->params()->fromRoute('account'));
        $organisationUnit = $this->getOrganisationUnitForAccount($account)->getRootEntity();
        $shippingLedger = $this->getShippingLedgerForOrganisationUnit($organisationUnit);


        try {
            $this->shippingLedgerService->topUpLedger($shippingLedger, $organisationUnit);
        } catch (ShippingLedgerTopUpException $e) {
            return $this->jsonModelFactory->newInstance([
                'success' => false,
                'balance' => $shippingLedger->getBalance(),
                'error' => 'Unable to confirm if payment was successful, please contact us to resolve this.',
            ]);
        }

        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'balance' => $shippingLedger->getBalance(),
            'error' => '',
        ]);
    }

    public function saveAction()
    {
        $input = $this->params()->fromPost();
        $account = $this->getAccount($this->params()->fromRoute('account'));
        $shippingLedger = $this->getShippingLedgerForAccount($account);
        $shippingLedger->setAutoTopUp($input['autoTopUp']);

        try {
            $this->shippingLedgerService->save($shippingLedger);
        } catch (NotModified $exception) {
            // Nothing to see here
        }

        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'error' => '',
        ]);
    }

    protected function getAccount($accountId): Account
    {
        return $this->accountService->fetch($accountId);
    }

    protected function getShippingLedgerForOrganisationUnit(OrganisatuonUnit $organisationUnit): ShippingLedger
    {
        return $this->shippingLedgerService->fetch($organisationUnit->getId());
    }

    protected function getOrganisationUnitForAccount(Account $account): OrganisationUnit
    {
        return $this->organisationUnitService->fetch($account->getOrganisationUnitId());
    }
}