<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use Settings\Controller\AddChannelSpecificVariablesToViewInterface;
use ShipStation\Account\ChannelSpecificVariables\Factory;
use Zend\View\Model\ViewModel;

class ShipStationController implements AddChannelSpecificVariablesToViewInterface
{
    /** @var Factory */
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        $courierSpecificVariablesHandler = ($this->factory)($account);
        $courierView = $courierSpecificVariablesHandler();
        if ($courierView) {
            $view->addChild($courierView, 'courierView');
        }
    }
}