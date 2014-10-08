<?php
namespace Settings\Controller;

use CG\Ekm\Credentials;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Ekm\Account as EkmAccount;
use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\Type as ChannelType;
use Zend\Di\Di;
use CG\Account\Client\Service as AccountService;
use Settings\Module;

class EkmController extends AbstractActionController
{
    const ROUTE_AJAX = 'ajax';

    protected $ekmAccount;
    protected $di;
    protected $accountService;
    protected $activeUserContainer;
    protected $jsonModelFactory;
    protected $viewModelFactory;

    public function __construct(
        EkmAccount $ekmAccount,
        Di $di,
        AccountService $accountService,
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory
    ) {
        $this->setEkmAccount($ekmAccount)
            ->setDi($di)
            ->setAccountService($accountService)
            ->setActiveUserContainer($activeUserContainer)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory );
    }

    public function indexAction()
    {
        $index = $this->getViewModelFactory()->newInstance();
        $index->setTemplate('settings/channel/ekm');
        $index->setVariable('isHeaderBarVisible', false);
        $index->setVariable('subHeaderHide', true);
        $index->setVariable('isSidebarVisible', false);
        $index->setVariable('accountId', $this->params()->fromQuery('accountId'));
        $index->addChild($this->getUsernameView(), 'username');
        $index->addChild($this->getPasswordView(), 'password');
        $index->addChild($this->getLinkAccountView(), 'linkAccount');
        $index->addChild($this->getGoBackView(), 'goBack');
        return $index;
    }

    protected function getUsernameView()
    {
        $username = $this->getViewModelFactory()->newInstance([
            'name' => 'ekm-username',
            'id' => 'ekm-username'
        ]);
        $username->setTemplate('elements/text.mustache');
        return $username;
    }

    protected function getPasswordView()
    {
        $password = $this->getViewModelFactory()->newInstance([
            'name' => 'ekm-password',
            'id' => 'ekm-password',
            'type' => 'password'
        ]);
        $password->setTemplate('elements/text.mustache');
        return $password;
    }

    protected function getLinkAccountView()
    {
        $linkAccount = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => 'Link Account',
            'id' => 'ekm-link-account'
        ]);
        $linkAccount->setTemplate('elements/buttons.mustache');
        return $linkAccount;
    }

    protected function getGoBackView()
    {
        $linkAccount = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => 'Go Back',
            'id' => 'ekm-go-back'
        ]);
        $linkAccount->setTemplate('elements/buttons.mustache');
        return $linkAccount;
    }

    public function saveAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        if ($accountId = $this->params()->fromPost('accountId')) {
            $accountEntity = $this->getAccountService()->fetch($accountId);
        } else {
            $accountEntity = $this->getDi()->newInstance(AccountEntity::class, array(
                "channel" => 'ekm',
                "organisationUnitId" => $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
                "displayName" => $this->params()->fromPost('username'),
                "credentials" => '',
                "active" => true,
                "deleted" => false,
                "type" => ChannelType::SALES
            ));
        }
        $credentials = new Credentials();
        $credentials->setUsername($this->params()->fromPost('username'))
            ->setPassword($this->params()->fromPost('password'));
        $accountEntity = $this->getEkmAccount()->save(
            $accountEntity,
            $credentials
        );
        $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
        $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId(), "type" => ChannelType::SALES]);
        $view->setVariable('redirectUrl', $url);
        return $view;
    }

    protected function setEkmAccount(EkmAccount $ekmAccount)
    {
        $this->ekmAccount = $ekmAccount;
        return $this;
    }

    protected function getEkmAccount()
    {
        return $this->ekmAccount;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function getAccountService()
    {
        return $this->accountService;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    protected function getDi()
    {
        return $this->di;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }
}