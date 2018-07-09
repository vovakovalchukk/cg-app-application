<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Billing\Shipping\Ledger\Entity as ShippingLedger;
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
use CG\Clearbooks\Payment\PaymentService;

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
    /** @var PaymentService */
    protected $clearBooksPaymentService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ShippingLedgerService $shippingLedgerService,
        AccountService $accountService,
        OrganisationUnitService $organisationUnitService,
        PaymentService $clearBooksPaymentService
    )
    {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->shippingLedgerService = $shippingLedgerService;
        $this->accountService = $accountService;
        $this->organisationUnitService = $organisationUnitService;
        $this->clearBooksPaymentService = $clearBooksPaymentService;
    }

    public function topupAction()
    {
        $account = $this->getAccount($this->params()->fromRoute('account'));
        $shippingLedger = $this->getShippingLedgerForAccount($account);
        $organisationUnit = $this->getOrganisationUnitForAccount($account);

        if (
        $this->clearBooksPaymentService->takeOneOffPaymentForCustomerAndAccountCode(
            $organisationUnit, $shippingLedger->getClearbooksCustomerId(), static::SHIPPING_CLEARBOOKS_ACCOUNT_CODE, static::DEFAULT_TOPUP_AMMOUNT, static::USPS_INVOICE_DESCRIPTION, static::USPS_ITEM_DESCRIPTION)
        )
        {
            return $this->jsonModelFactory->newInstance([
                'success' => true,
                'balance' => $shippingLedger->getBalance(),
                'error' => '',
            ]);

        }
        return $this->jsonModelFactory->newInstance([
            'success' => false,
            'balance' => $shippingLedger->getBalance(),
            'error' => 'Unable to confirm if payment was successful, please contact us to resolve this.',
        ]);
    }

    public function saveAction()
    {
        $autoTopUp = $this->params()->fromPost('autoTopUp');
        $account = $this->getAccount($this->params()->fromRoute('account'));
        $shippingLedger = $this->getShippingLedgerForAccount($account);
        $shippingLedger->setAutoTopUp(($autoTopUp === "true") ? true : false);

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

    protected function getShippingLedgerForAccount(Account $account): ShippingLedger
    {
        return $this->shippingLedgerService->fetch($account->getOrganisationUnitId());
    }

    protected function getOrganisationUnitForAccount(Account $account): OrganisationUnit
    {
        return $this->organisationUnitService->fetch($account->getOrganisationUnitId());
    }

    protected function addTransactionAmountToExistingBalance(Transaction $transaction, ShippingLedger $shippingLedger) {
        $newBalance = $shippingLedger->getBalance() + $transaction->getAmount();
        $shippingLedger->setBalance((float)$newBalance);
        $this->shippingLedgerService->save($shippingLedger);
    }
}