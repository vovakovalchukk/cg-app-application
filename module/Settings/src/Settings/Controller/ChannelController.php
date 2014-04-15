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
use Settings\Channel\Mapper;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use CG\Channel\Service as ChannelService;
use Mustache\View\Renderer as MustacheRenderer;
use CG_UI\Form\Factory as FormFactory;
use CG\Zend\Stdlib\View\Model\Exception as ViewModelException;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\I18n\Translator\Translator;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Account\Client\Service as AccountService;
use CG\User\Entity as User;
use DirectoryIterator;

class ChannelController extends AbstractActionController
{
    const ACCOUNT_ROUTE = "Manage";
    const ACCOUNT_DELETE_ROUTE = "Delete";
    const ACCOUNT_AJAX_ROUTE = "Sales Channel Item Ajax";
    const ROUTE = "Sales Channels";
    const AJAX_ROUTE = "ajax";
    const CREATE_ROUTE = "Sales Channel Create";
    const ACCOUNT_TEMPLATE = "Sales Channel Item";
    const ACCOUNT_CHANNEL_FORM_BLANK_TEMPLATE = "Sales Channel Item Channel Form Blank";
    const ACCOUNT_DETAIL_FORM = "Sales Channel Item Detail";

    protected $di;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $accountService;
    protected $accountFactory;
    protected $activeUserContainer;
    protected $service;
    protected $mapper;
    protected $channelService;
    protected $mustacheRenderer;
    protected $formFactory;
    protected $translator;

    public function __construct(
        Di $di,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        AccountService $accountService,
        AccountFactory $accountFactory,
        ActiveUserInterface $activeUserContainer,
        Service $service,
        Mapper $mapper,
        ChannelService $channelService,
        MustacheRenderer $mustacheRenderer,
        FormFactory $formFactory,
        Translator $translator
    ) {
        $this->setDi($di)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setAccountService($accountService)
            ->setAccountFactory($accountFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setService($service)
            ->setMapper($mapper)
            ->setChannelService($channelService)
            ->setMustacheRenderer($mustacheRenderer)
            ->setFormFactory($formFactory)
            ->setTranslator($translator);
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

    public function setMapper(Mapper$mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
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
     * @param $variables
     * @param $options
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
        $list->addChild(
            $this->getAccountList(),
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

    protected function getAccountList()
    {
        $accountList = $this->getService()->getAccountList();
        $settings = $accountList->getVariable('settings');

        $settings->setSource(
            $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE . '/' . static::AJAX_ROUTE)
        );

        $settings->setTemplateUrlMap($this->mustacheTemplateMap('accountList'));

        return $accountList;
    }

    public function listAjaxAction()
    {
        $data = [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => [],
        ];

        $limit = 'all';
        $page = 1;
        if ($this->params()->fromPost('iDisplayLength') > 0) {
            $limit = $this->params()->fromPost('iDisplayLength');
            $page += floor($this->params()->fromPost('iDisplayStart') / $limit);
        }

        try {
            $accounts = $this->getAccountService()->fetchByOU(
                $this->getActiveUser()->getOuList(),
                $limit,
                $page
            );

            $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int) $accounts->getTotal();

            foreach ($accounts as $account) {
                $data['Records'][] = $this->getMapper()->toDataTableArray($account, $this->url());
            }
        } catch (NotFound $exception) {
            // No accounts so ignoring
        }

        return $this->newJsonModel($data);
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
        $returnRoute = Module::ROUTE . '/' . static::ROUTE;
        $channelSpecificTemplate = $this->getService()->getChannelSpecificTemplateNameForAccount($accountEntity);
        $channelSpecificView = $this->newViewModel();
        $channelSpecificView->setTemplate($channelSpecificTemplate);
        $formName = $this->getService()->getChannelSpecificFormNameForAccount($accountEntity);
        $form = $this->getFormFactory()->get($formName);
        $form->get('account')->setValue($accountEntity->getId());
        $form->get('route')->setValue($returnRoute);
        $channelSpecificView->setVariables([
            'form' => $form,
            'account' => $accountEntity,
            'route' => $returnRoute
        ]);
        $view->addChild($channelSpecificView, 'channelSpecificForm');
        return $this;
    }

    protected function addAccountDetailsForm($accountEntity, $view)
    {
        $accountForm = $this->getFormFactory()->get(static::ACCOUNT_DETAIL_FORM);
        $accountForm->setData($accountEntity->toArray());
        $updateUrl = $this->url()->fromRoute(
            Module::ROUTE.'/'.static::ROUTE.'/'.static::ACCOUNT_ROUTE.'/'.static::ACCOUNT_AJAX_ROUTE,
            ['account' => $accountEntity->getId()]
        );
        $accountForm->setAttribute('action', $updateUrl);
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
        $tradingCompanyView->setVariable('options', $tradingCompanyOptions);
        $view->setVariable('tradingCompanySelect', $this->getMustacheRenderer()->render($tradingCompanyView));
        return $this;
    }

    public function accountUpdateAction()
    {
        $id = $this->params('account');
        $postData = $this->getRequest()->getPost();
        $displayName = $postData->get('displayName');
        $organisationUnitId = $postData->get('organisationUnitId');

        try {
            $this->getService()->updateAccount($id, compact('displayName', 'organisationUnitId'));
            $response = $this->getJsonModelFactory()->newInstance();
            $response->setVariable('valid', true);
            $response->setVariable('status', 'Channel account updated');
            return $response;
        } catch (NotFound $e) {
            $this->handleAccountUpdateException($e, 'That channel account could not be found and so could not be updated');
        } catch (NotModified $e) {
            $this->handleAccountUpdateException($e, 'There were no changes to be saved');
        }
    }

    protected function handleAccountUpdateException(\Exception $e, $message)
    {
        $status = $this->getJsonModelFactory()->newInstance();
        $status->setVariable('valid', false);
        throw new ViewModelException(
            $status,
            $this->getTranslator()->translate($message),
            $e->getCode(),
            $e
        );
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

    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @return AccountService
     */
    public function getAccountService()
    {
        return $this->accountService;
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

    /**
     * @param $variables
     * @param $options
     * @return JsonModel
     */
    protected function newJsonModel($variables = null, $options = null)
    {
        return $this->getJsonModelFactory()->newInstance($variables, $options);
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

    /**
     * @return User
     */
    public function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
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

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    public function setFormFactory(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }
}