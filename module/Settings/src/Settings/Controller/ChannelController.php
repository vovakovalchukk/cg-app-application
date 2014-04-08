<?php
namespace Settings\Controller;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountFactory;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use Settings\Module;
use Zend\Di\Di;
use Zend\Mvc\Controller\AbstractActionController;
use Settings\Channel\Service;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\View\Model\ViewModel;
use CG\Channel\Service as ChannelService;
use Settings\Form\AccountDetailsForm;
use Mustache\View\Renderer as MustacheRenderer;

class ChannelController extends AbstractActionController
{
    protected $di;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $accountFactory;
    protected $activeUserContainer;
    protected $service;
    protected $channelService;
    protected $mustacheRenderer;

    const ACCOUNT_ROUTE = "Sales Channel Item";
    const ROUTE = "Sales Channels";
    const CREATE_ROUTE = "Sales Channel Create";
    const ACCOUNT_TEMPLATE = "Sales Channel Item";
    const ACCOUNT_CHANNEL_FORM_BLANK_TEMPLATE = "Sales Channel Item Channel Form Blank";

    public function __construct(
        Di $di,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        AccountFactory $accountFactory,
        ActiveUserInterface $activeUserContainer,
        Service $service,
        ChannelService $channelService,
        MustacheRenderer $mustacheRenderer
    ) {
        $this->setDi($di)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setAccountFactory($accountFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setService($service)
            ->setChannelService($channelService)
            ->setMustacheRenderer($mustacheRenderer);
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
        $list->addChild(
            $this->getService()->getAccountList(),
            'accountList'
        );
        $channels = $this->newViewModel();
        $channels->setVariable('channels', $this->getChannelService()->getChannels());
        $channels->setTemplate('settings/channel/create/item');
        $list->addChild(
            $channels,
            'channels'
        );
        return $list;
    }

    public function accountAction()
    {
        $id = $this->params('account');
        $accountEntity = $this->getService()->getAccount($id);
        $view = $this->newViewModel();
        $view->setTemplate(static::ACCOUNT_TEMPLATE);
        $view->setVariable('account', $accountEntity);

        $this->addAccountsChannelSpecificView($accountEntity, $view)
            ->addAccountDetailsForm($accountEntity, $view)
            ->addTradingCompaniesView($accountEntity, $view);

        return $view;
    }

    protected function addAccountsChannelSpecificView($accountEntity, $view)
    {
        $channelSpecificTemplate = $this->getService()->getChannelSpecificTemplateForAccount($accountEntity);
        $channelSpecificView = $this->newViewModel();
        $channelSpecificView->setTemplate($channelSpecificTemplate);
        $view->addChild($channelSpecificView, 'channelSpecificForm');
        return $this;
    }

    protected function addAccountDetailsForm($accountEntity, $view)
    {
        $accountForm = $this->getDi()->get(AccountDetailsForm::class, ['account' => $accountEntity]);
        $view->setVariable('detailsForm', $accountForm);
        return $this;
    }

    protected function addTradingCompaniesView($accountEntity, $view)
    {
        $tradingCompanies = $this->getService()->getTradingCompanyOptionsForAccount($accountEntity);
        $tradingCompanyOptions = [];
        foreach ($tradingCompanies as $tradingCompany) {
            $tradingCompanyOptions[] = [
                'value' => $tradingCompany->getId(),
                'title' => $tradingCompany->getAddressCompanyName(),
                'selected' => ($tradingCompany->getId() == $accountEntity->getOrganisationUnitId())
            ];
        }
        $tradingCompanyView = $this->newViewModel();
        $tradingCompanyView->setTemplate('elements/custom-select');
        $tradingCompanyView->setVariable('options', [
            'options' => $tradingCompanyOptions
        ]);
        $view->setVariable('tradingCompanySelect', $this->getMustacheRenderer()->render($tradingCompanyView));
        return $this;
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
            "channel" => $this->params()->fromPost('channel'),
            "organisationUnitId" => $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            "displayName" => "",
            "credentials" => "",
            "active" => false,
            "deleted" => false,
            "expiryDate" => null
        ));
        $view = $this->getJsonModelFactory()->newInstance();
        $url = $this->getAccountFactory()->createRedirect($accountEntity, Module::ROUTE . '/' . static::ROUTE,
            $this->params()->fromPost('region'));
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

    public function setChannelService(ChannelService $channelService)
    {
        $this->channelService = $channelService;
        return $this;
    }

    public function getChannelService()
    {
        return $this->channelService;
    }

    public function getMustacheRenderer()
    {
        return $this->mustacheRenderer;
    }

    public function setMustacheRenderer(MustacheRenderer $mustacheRenderer)
    {
        $this->mustacheRenderer = $mustacheRenderer;
        return $this;
    }
}