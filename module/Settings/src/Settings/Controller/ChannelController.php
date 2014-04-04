<?php
namespace Settings\Controller;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountFactory;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Di\Di;
use Zend\Mvc\Controller\AbstractActionController;
use Settings\Channel\Service;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\View\Model\ViewModel;

class ChannelController extends AbstractActionController
{
    protected $di;
    protected $jsonModelFactory;
    protected $accountFactory;
    protected $activeUserContainer;
    protected $viewModelFactory;
    protected $service;

    const ACCOUNT_ROUTE = "Sales Channel Item";
    const ROUTE = "Sales Channels";

    public function __construct(
        Di $di,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        AccountFactory $accountFactory,
        ActiveUserInterface $activeUserContainer,
        Service $service
    )
    {
        $this->setDi($di)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setAccountFactory($accountFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setService($service);
    }

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }


    public function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    public function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    /**
     * @param null $variables
     * @param null $options
     * @return ViewModel
     */
    protected function newViewModel($variables = null, $options = null)
    {
        return $this->getViewModelFactory()->newInstance($variables, $options);
    }

    public function listAction()
    {
        $list = $this->newViewModel();
        $list->setVariable(
            'title',
            $this->getRouteName()
        );
        $list->setVariable(
            'newChannelForm',
            $this->getService()->getNewChannelForm()
        );
        return $list;
    }

    protected function getRouteName()
    {
        $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $routeParts = explode('/', $route);
        return end($routeParts);
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
        $url = $this->getAccountFactory()->createRedirect($accountEntity, static::ROUTE . '/' . IndexController::ROUTE,
            $this->params()->fromQuery('region'));
        $view->setVariable('url', $url);
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