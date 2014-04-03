<?php
namespace Settings\Controller;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountFactory;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Di\Di;
use Zend\Mvc\Controller\AbstractActionController;

class ChannelController extends AbstractActionController
{
    protected $di;
    protected $jsonModelFactory;
    protected $accountFactory;
    protected $activeUserContainer;

    const ACCOUNT_ROUTE = "Sales Channel Item";

    public function __construct(
        Di $di,
        JsonModelFactory $jsonModelFactory,
        AccountFactory $accountFactory,
        ActiveUserInterface $activeUserContainer
    )
    {
        $this->setDi($di)
            ->setJsonModelFactory($jsonModelFactory)
            ->setAccountFactory($accountFactory)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function listAction()
    {

    }

    public function createAction()
    {
        $accountEntity = $this->getDi()->newInstance(AccountEntity::class, array(
            "channel" => $this->params()->fromQuery('channel'),
            "organisationUnitId" => $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            "displayName" => "",
            "credentials" => "",
            "active" => false,
            "deleted" => false,
            "expiryDate" => null
        ));
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable('url', $this->getAccountFactory()->createRedirect($accountEntity));
        return $view;
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

    public function setAccountFactory(AccountFactory $accountFactory)
    {
        $this->accountFactory = $accountFactory;
        return $this;
    }

    public function getAccountFactory()
    {
        return $this->accountFactory;
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

    public function setActiveUserContainer($activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }
}