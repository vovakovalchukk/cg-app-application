<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Clearbooks\Invoice\Statement;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\AddChannelSpecificVariablesToViewInterface;
use ShipStation\Account\ChannelSpecificVariables\Factory;
use Zend\View\Model\ViewModel;

class ShipStationController implements AddChannelSpecificVariablesToViewInterface
{
    /** @var ShippingLedgerService */
    protected $shippingLedgerService;
    protected $viewModelFactory;
    protected $statement;
    /** @var Factory */
    protected $factory;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ShippingLedgerService $shippingLedgerService,
        Statement $statement,
        Factory $factory
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->shippingLedgerService = $shippingLedgerService;
        $this->statement = $statement;
        $this->factory = $factory;
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        $courierSpecificVariablesHandler = ($this->factory)($account);
        $courierView = $courierSpecificVariablesHandler();
        if ($courierView) {
            $view->addChild($courierView, 'courierView');
        }

        // TODO: move this into a Usps class called by the above factory
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