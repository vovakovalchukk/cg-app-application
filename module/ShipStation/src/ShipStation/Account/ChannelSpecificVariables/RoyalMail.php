<?php
namespace ShipStation\Account\ChannelSpecificVariables;

use CG\Account\Shared\Entity as Account;
use CG_UI\View\Prototyper\ViewModelFactory;
use ShipStation\Account\ChannelSpecificVariablesInterface;
use Zend\View\Model\ViewModel;

class RoyalMail implements ChannelSpecificVariablesInterface
{
    /** @var Account */
    protected $account;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(Account $account, ViewModelFactory $viewModelFactory)
    {
        $this->account = $account;
        $this->viewModelFactory = $viewModelFactory;
    }

    public function __invoke(): ?ViewModel
    {
        $royalMailView = $this->viewModelFactory->newInstance([
            'isAccountPending' => $this->account->getPending()
        ]);
        $royalMailView->setTemplate('ship-station/settings_account/royal-mail');
        return $royalMailView;
    }
}