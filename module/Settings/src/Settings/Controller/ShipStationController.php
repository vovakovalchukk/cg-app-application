<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use Settings\Controller\AddChannelSpecificVariablesToViewInterface;
use Zend\View\Model\ViewModel;

class ShipStationController implements AddChannelSpecificVariablesToViewInterface
{
    /** @var ShippingLedgerService */
    protected $shippingLedgerService;

    public function __construct(
        ShippingLedgerService $shippingLedgerService
    )
    {
        $this->shippingLedgerService = $shippingLedgerService;
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        if ($account->getChannel() != 'usps-ss') {
            return;
        }
        $shippingLedger = $this->shippingLedgerService->fetch($account->getRootOrganisationUnitId());
        $view->setVariables([
            'balance' => $shippingLedger->getBalance(),
            'autoTopUp' => $shippingLedger->isAutoTopUp(),
            'topUpAmount' => 100,
            'currencySymbol' => '$',
            'clearbooksStatementUrl' => $shippingLedger->getClearbooksStatementUrl(),
        ]);
    }
}