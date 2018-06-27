<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use Settings\Controller\AddChannelSpecificVariablesToViewInterface;
use Zend\View\Model\ViewModel;

class ShipStationController implements AddChannelSpecificVariablesToViewInterface
{
    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        // This dummy data and code will be replaced and refactored in TAC-121
        if ($account->getChannel() != 'usps-ss') {
            return;
        }
        $view->setVariables([
            'balance' => 10,
            'autoTopUp' => false,
            'topUpAmount' => 100,
            'currencySymbol' => '$',
            'clearbooksStatementUrl' => 'https://www.clearbooks.co.uk/',
        ]);
    }
}