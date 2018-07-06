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
use Settings\Channel\Service as ChannelService;
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
    /** @var ChannelService */
    protected $channelService;
    /** @var OneOffPaymentService */
    protected $oneOffPaymentService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ShippingLedgerService $shippingLedgerService,
        ChannelService $channelService,
        OneOffPaymentService $oneOffPaymentService,
        OrganisationUnitService $organisationUnitService
    )
    {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->shippingLedgerService = $shippingLedgerService;
        $this->channelService = $channelService;
        $this->oneOffPaymentService = $oneOffPaymentService;
        $this->organisationUnitService = $organisationUnitService;
    }

    public function topupAction()
    {
        $account = $this->getAccount($this->params()->fromRoute('account'));
        $shippingLedger = $this->getShippingLedgerForAccount($account);
        $organisationUnit = $this->getOrganisationUnitForAccount($account);

        $this->logInfo('Attempting to top-up shipping account for OU: %s', [$organisationUnit->getOrganisationUnitId()], static::LOG_CONSTANT);

        try {
            $transaction = $this->oneOffPaymentService->takeOneOffPayment(
                null,
                $organisationUnit,
                static::DEFAULT_TOPUP_AMMOUNT,
                static::USPS_INVOICE_DESCRIPTION,
                static::USPS_ITEM_DESCRIPTION,
                new \DateTime(),
                $shippingLedger->getClearbooksCustomerId(),
                static::SHIPPING_CLEARBOOKS_ACCOUNT_CODE
            );
            if ($transaction->getStatus() == TransactionStatus::STATUS_PAID) {
                $this->addTransactionAmountToExistingBalance($transaction, $shippingLedger);
            } else {
                $this->logInfo('Failed to confirm payment for shipping account top-up for OU: %s', [$organisationUnit->getOrganisationUnitId()], static::LOG_CONSTANT);
                throw new FailedPaymentException('Unable to confirm if payment was successful, please contact us to resolve this.');
            }
        } catch (FailedPaymentException $exception) {
            $this->logException($exception, 'error', __NAMESPACE__);
            return $this->jsonModelFactory->newInstance([
                'success' => false,
                'balance' => $shippingLedger->getBalance(),
                'error' => $exception->getMessage(),
            ]);
        }

        $this->logInfo('Successfully topped-up shipping account for OU: %s', [$organisationUnit->getOrganisationUnitId()], static::LOG_CONSTANT);

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
        return $this->channelService->getAccount($accountId);
    }

    protected function getShippingLedgerForAccount(Account $account): ShippingLedger
    {
        return $this->shippingLedgerService->fetch($account->getId());
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