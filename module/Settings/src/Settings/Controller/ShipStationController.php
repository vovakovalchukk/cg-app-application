<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use CG\Billing\Shipping\Ledger\Entity as ShippingLedger;
use Settings\Controller\AddChannelSpecificVariablesToViewInterface;
use Zend\View\Model\ViewModel;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Clearbooks\Invoice\Statement;

class ShipStationController implements AddChannelSpecificVariablesToViewInterface
{
    /** @var ShippingLedgerService */
    protected $shippingLedgerService;
    protected $viewModelFactory;
    protected $statement;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ShippingLedgerService $shippingLedgerService,
        Statement $statement
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->shippingLedgerService = $shippingLedgerService;
        $this->statement = $statement;
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        if ($account->getChannel() != 'usps-ss') {
            return;
        }
        $shippingLedger = $this->shippingLedgerService->fetch($account->getRootOrganisationUnitId());
        $view->setVariables([
            'clearbooksStatementUrl' => $this->statement->getSecureUrlFromInsecureUrl($shippingLedger->getClearbooksStatementUrl())
        ])->addChild($this->getShippingLedgerTopUpView($shippingLedger, $account), 'shippingLedgerTopUp');
    }

    protected function getShippingLedgerTopUpView(ShippingLedger $shippingLedger, Account $account)
    {
        $config = [
            'accountId' => $account->getId(),
            'showStatus' => true,
            'shippingLedgerBalance' => [
                'balance' => $shippingLedger->getBalance(),
                'topUpAmount' => 100,
                'currencySymbol' => '$',
            ],
            'autoTopUp' => [
                'id' => 'autoTopUp',
                'name' => 'autoTopUp',
                'selected' => $shippingLedger->isAutoTopUp(),
                'class' => 'autoTopUp'
            ],
            'tooltip' => [
                'id' => 'autoTopUpTooltip',
                'name' => 'autoTopUpTooltip',
                'attach' => '#topupTooltip',
                'content' => 'When automatic top-ups are enabled ChannelGrabber will automatically purchase $100 of UPS postage when your balance drops below $100',
            ]
        ];

        return $this->viewModelFactory->newInstance($config)->setTemplate('shippingLedgerTopUp.mustache');
    }
}