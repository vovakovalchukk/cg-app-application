<?php
namespace ShipStation\Account\ChannelSpecificVariables;

use CG\Account\Shared\Entity as Account;
use CG\Billing\Shipping\Ledger\Entity as ShippingLedger;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use CG_UI\View\Prototyper\ViewModelFactory;
use ShipStation\Account\ChannelSpecificVariablesInterface;
use Zend\View\Model\ViewModel;

class Usps implements ChannelSpecificVariablesInterface
{
    /** @var Account */
    protected $account;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var ShippingLedgerService */
    protected $shippingLedgerService;

    public function __construct(
        Account $account,
        ViewModelFactory $viewModelFactory,
        ShippingLedgerService $shippingLedgerService
    ) {
        $this->account = $account;
        $this->viewModelFactory = $viewModelFactory;
        $this->shippingLedgerService = $shippingLedgerService;
    }

    public function __invoke(): ?ViewModel
    {
        $shippingLedger = $this->shippingLedgerService->fetch($this->account->getRootOrganisationUnitId());
        $uspsView = $this->viewModelFactory->newInstance();
        $uspsView->setTemplate('ship-station/settings_account/usps');
        $uspsView->setVariables([
            'clearbooksStatementUrl' => $shippingLedger->getClearbooksStatementUrl()
        ])->addChild($this->getShippingLedgerTopUpView($shippingLedger), 'shippingLedgerTopUp');
        return $uspsView;
    }

    protected function getShippingLedgerTopUpView(ShippingLedger $shippingLedger)
    {
        $config = [
            'accountId' => $this->account->getId(),
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