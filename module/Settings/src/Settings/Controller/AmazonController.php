<?php
namespace Settings\Controller;

use CG\Account\CreationServiceAbstract as AccountCreationService;
use CG\Account\Shared\Entity as Account;
use CG\Amazon\Message\AccountAddressGenerator;
use CG\Channel\Type as ChannelType;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Module;
use Zend\View\Model\ViewModel;

class AmazonController extends ChannelControllerAbstract
{
    protected $accountAddressGenerator;

    public function __construct(
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        AccountAddressGenerator $accountAddressGenerator
    ) {
        parent::__construct($accountCreationService, $activeUserContainer, $jsonModelFactory, $viewModelFactory);
        $this->setAccountAddressGenerator($accountAddressGenerator);
    }

    public function saveAction()
    {
        $accountEntity = $this->getAccountCreationService()->connectAccount(
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            $this->params()->fromQuery('accountId'),
            array_merge($this->params()->fromPost(), $this->params()->fromRoute())
        );
        $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
        $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId(), "type" => ChannelType::SALES]);
        $this->plugin('redirect')->toUrl($url);
        return false;
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        $addressGenerator = $this->accountAddressGenerator;
        $address = $addressGenerator($account);
        $view->setVariable('messagesAddress', $address);
    }

    protected function setAccountAddressGenerator(AccountAddressGenerator $accountAddressGenerator)
    {
        $this->accountAddressGenerator = $accountAddressGenerator;
        return $this;
    }
}