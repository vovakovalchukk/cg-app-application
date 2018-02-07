<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use Zend\View\Model\ViewModel;

interface AddChannelSpecificVariablesToViewInterface
{
    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view);
}