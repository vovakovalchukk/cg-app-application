<?php
namespace Settings\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Client\Entity as AccountEntity;
use CG\Ebay\Account as EbayAccount;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Di\Di;
use Zend\Mvc\Controller\AbstractActionController;
use Settings\Module;

class EbayController extends AbstractActionController
{
    protected $di;
    protected $jsonModelFactory;
    protected $ebayAccount;
    protected $activeUserContainer;
    protected $accountService;

    public function __construct(
        Di $di,
        JsonModelFactory $jsonModelFactory,
        EbayAccount $ebayAccount,
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService
    )
    {
        $this->setDi($di)
            ->setJsonModelFactory($jsonModelFactory)
            ->setEbayAccount($ebayAccount)
            ->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService);
    }

    public function saveAction()
    {
        if ($this->params()->fromQuery('accountId')) {
            $accountEntity = $this->getAccountService()->fetch($this->params()->fromQuery('accountId'));
        } else {
            $accountEntity = $this->getDi()->newInstance(AccountEntity::class, array(
                "channel" => "ebay",
                "organisationUnitId" => $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
                "displayName" => $this->params()->fromQuery('username'),
                "credentials" => "",
                "active" => true,
                "deleted" => false,
                "expiryDate" => null,
                "type" => "sale"
            ));
        }
        $accountEntity = $this->getEbayAccount()->save($this->params()->fromQuery('sessionId'), $accountEntity);
        $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_SALES, ChannelController::ACCOUNT_ROUTE]);
        $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId()]);
        $this->plugin('redirect')->toUrl($url);
        return false;
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
    }

    public function setEbayAccount(EbayAccount $ebayAccount)
    {
        $this->ebayAccount = $ebayAccount;
        return $this;
    }

    public function getEbayAccount()
    {
        return $this->ebayAccount;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    public function getAccountService()
    {
        return $this->accountService;
    }
}