<?php
namespace Settings\Controller;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\Client\Service as AccountService;
use CG\Channel\AccountFactory;
use CG\Channel\GetNamespacePartForAccountTrait;
use CG\Channel\Service as ChannelService;
use CG\Channel\Shipping\Provider\Channels\Repository as ShippingProviderChannelsRepository;
use CG\Channel\Type;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\Zend\Stdlib\Mvc\Controller\ExceptionToViewModelUserExceptionTrait;
use CG_Mustache\View\Renderer as MustacheRenderer;
use CG_UI\Form\Factory as FormFactory;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Channel\Service;
use Settings\Channel\Mapper;
use Settings\Module;
use Zend\Di\Di;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\I18n\Translator\Translator;
use SplObjectStorage;

class ChannelController extends AbstractActionController
{
    use ExceptionToViewModelUserExceptionTrait;
    use GetNamespacePartForAccountTrait;

    const ROUTE_ACCOUNT = "Manage";
    const ROUTE_ACCOUNT_STATUS = 'Status';
    const ROUTE_ACCOUNT_STOCK_MANAGEMENT = 'Stock Management';
    const ROUTE_ACCOUNT_AJAX = "Sales Channel Item Ajax";
    const ROUTE = "Channel Management";
    const ROUTE_CHANNELS = "Channels";
    const ROUTE_AJAX = "ajax";
    const ROUTE_CREATE = "create";
    const ACCOUNT_TEMPLATE = "Sales Channel Item";
    const ACCOUNT_CHANNEL_FORM_BLANK_TEMPLATE = "Sales Channel Item Channel Form Blank";
    const ACCOUNT_DETAIL_FORM = "Sales Channel Item Detail";
    const ACCOUNT_TYPE = 'type';
    const ACCOUNT_TYPE_TO_LIST = 'sale';
    const EVENT_ACCOUNT_STATUS_CHANGED = 'Account Status Changed';
    const EVENT_ACCOUNT_STOCK_MANAGEMENT_CHANGED = 'Account Stock Management Changed';
    const EVENT_ACCOUNT_DELETED = 'Account Deleted';

    protected $di;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $accountService;
    protected $activeUserContainer;
    protected $service;
    protected $mapper;
    protected $channelService;
    protected $mustacheRenderer;
    protected $formFactory;
    protected $translator;
    protected $organisationUnitService;
    protected $intercomEventService;
    /** @var ShippingProviderChannelsRepository */
    protected $shippingProviderChannelsRepository;
    /** @var AccountFactory */
    protected $accountFactory;

    public function __construct(
        Di $di,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        AccountService $accountService,
        ActiveUserInterface $activeUserContainer,
        Service $service,
        Mapper $mapper,
        ChannelService $channelService,
        MustacheRenderer $mustacheRenderer,
        FormFactory $formFactory,
        Translator $translator,
        OrganisationUnitService $organisationUnitService,
        IntercomEventService $intercomEventService,
        ShippingProviderChannelsRepository $shippingProviderChannelsRepository,
        AccountFactory $accountFactory
    ) {
        $this->setDi($di)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setAccountService($accountService)
            ->setActiveUserContainer($activeUserContainer)
            ->setService($service)
            ->setMapper($mapper)
            ->setChannelService($channelService)
            ->setMustacheRenderer($mustacheRenderer)
            ->setFormFactory($formFactory)
            ->setTranslator($translator)
            ->setOrganisationUnitService($organisationUnitService)
            ->setIntercomEventService($intercomEventService)
            ->setShippingProviderChannelsRepository($shippingProviderChannelsRepository)
            ->setAccountFactory($accountFactory);
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

    public function indexAction()
    {
        return $this->redirect()->toRoute(Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_CHANNELS, ['type' => Type::SALES]);
    }

    public function listAction()
    {
        $list = $this->newViewModel();
        $list->setVariable('title', $this->getRouteName())
             ->setVariable('createRoute', Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_CHANNELS.'/'.static::ROUTE_CREATE, ['type' => $this->params('type')])
             ->setVariable('type', $this->params('type'))
             ->addChild($this->getAccountList(), 'accountList')
             ->addChild($this->getAddChannelSelect(), 'addChannelSelect');
        $list->setVariable('isHeaderBarVisible', false);
        $list->setVariable('subHeaderHide', true);
        return $list;
    }

    protected function getAddChannelSelect()
    {
        $addChannelSelect = $this->newViewModel();
        $addChannelSelect->setTemplate('settings/channel/create/select');
        $includeDarkDeploy = $this->activeUserContainer->isAdmin();
        $addChannelSelect->setVariable('channels', $this->getChannelService()->getChannels($this->params('type'), $includeDarkDeploy));
        return $addChannelSelect;
    }

    protected function getAccountList()
    {
        $this->getService()->setupAccountList($this->params('type'));
        $accountList = $this->getService()->getAccountList();
        $settings = $accountList->getVariable('settings');
        $settings->setSource(
            $this->url()->fromRoute(Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_CHANNELS.'/'.static::ROUTE_AJAX, ['type' => $this->params('type')])
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
            $accounts = $this->getAccountService()->fetchByOUAndStatus(
                $this->getActiveUser()->getOuList(),
                null,
                false,
                $limit,
                $page,
                $this->params('type')
            );

            // Hack to hide amazon shipping accounts
            if ($this->params('type') == Type::SHIPPING) {
                $amazonAccounts = new SplObjectStorage();

                /** @var AccountEntity $account */
                foreach ($accounts as $account) {
                    if ($account->getChannel() == 'amazon') {
                        $amazonAccounts->attach($account);
                    }
                }

                $accounts->removeAll($amazonAccounts);
            }

            $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int) $accounts->getTotal();

            foreach ($accounts as $account) {
                $data['Records'][] = $this->filterDataTableArrayFields($account);
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
        $view->setVariable('account', $this->getMapper()->toDataTableArray($accountEntity, $this->url(), $this->params('type')));
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        $this->addChannelLogo($accountEntity, $view)
            ->addAccountsChannelSpecificView($accountEntity, $view)
            ->addAccountDetailsForm($accountEntity, $view)
            ->addTradingCompaniesView($accountEntity, $view);
        $view->setVariable('type', $this->params('type'));

        return $view;
    }

    protected function addChannelLogo($accountEntity, $view)
    {
        $logoView = $this->getViewModelFactory()->newInstance();
        $logoView->setTemplate("elements/channel-large.mustache");
        $logoView->setVariable('channel', $accountEntity->getChannel());
        $logoView->setVariable('channelImgUrl', $accountEntity->getImageUrl());

        $view->addChild($logoView, 'channel');
        return $this;
    }

    protected function addAccountsChannelSpecificView($accountEntity, $view)
    {
        $returnRoute = Module::ROUTE . '/' . static::ROUTE . '/' . static::ROUTE_CHANNELS;
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
            'route' => $returnRoute,
            'isAdmin'=> $this->activeUserContainer->isAdmin()
        ]);
        $this->addAccountsChannelSpecificVariablesToChannelSpecificView($accountEntity, $channelSpecificView);
        $view->addChild($channelSpecificView, 'channelSpecificForm');
        return $this;
    }

    protected function addAccountsChannelSpecificVariablesToChannelSpecificView(AccountEntity $account, ViewModel $view)
    {
        $channelController = $this->getChannelSpecificController($account);
        if (!$channelController) {
            return;
        }
        // Don't use is_callable() as there's a magic __get() method that fools that
        if (!method_exists($channelController, 'addAccountsChannelSpecificVariablesToChannelSpecificView')) {
            return;
        }
        $channelController->addAccountsChannelSpecificVariablesToChannelSpecificView($account, $view);
    }

    protected function getChannelSpecificController(AccountEntity $account)
    {
        $channelControllerClass = __NAMESPACE__ . '\\' . $this->getNamespacePartForAccount($account) . 'Controller';
        if (!class_exists($channelControllerClass)) {
            return null;
        }
        $channelController = $this->di->get($channelControllerClass);
        return $channelController;
    }

    protected function addAccountDetailsForm($accountEntity, $view)
    {
        $accountForm = $this->getFormFactory()->get(static::ACCOUNT_DETAIL_FORM);
        $accountForm->setData($accountEntity->toArray());
        $updateUrl = $this->url()->fromRoute(
            Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_CHANNELS.'/'.static::ROUTE_ACCOUNT.'/'.static::ROUTE_ACCOUNT_AJAX,
            ['account' => $accountEntity->getId(), 'type' => $this->params('type')]
        );
        $accountForm->setAttribute('action', $updateUrl);
        $view->setVariable('detailsForm', $accountForm);
        return $this;
    }

    protected function addTradingCompaniesView($accountEntity, $view)
    {
        $tradingCompanies = $this->getService()->getTradingCompanyOptionsForAccount($accountEntity);

        $organisationUnit = $this->getOrganisationUnitService()->fetch(
            $this->getActiveUser()->getOrganisationUnitId()
        );
        array_unshift($tradingCompanies, $organisationUnit);

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
        $tradingCompanyView->setVariable('name', 'organisationUnitId');
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

        if ($organisationUnitId == "") {
            $organisationUnitId = $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
        }

        try {
            $this->getService()->updateAccount($id, compact('displayName', 'organisationUnitId'));
        } catch (NotFound $e) {
            throw $this->exceptionToViewModelUserException($e, 'That channel account could not be found and so could not be updated');
        } catch (NotModified $e) {
            // display saved message
        }
        $response = $this->getJsonModelFactory()->newInstance();
        $response->setVariable('valid', true);
        $response->setVariable('status', 'Channel account updated');
        $response->setVariable('type', $this->params('type'));
        return $response;
    }

    protected function getRouteName()
    {
        $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $routeParts = explode('/', $route);
        return end($routeParts);
    }

    protected function notifyOfChange($change, AccountEntity $accountEntity)
    {
        $event = new IntercomEvent(
            $change,
            $this->getActiveUser()->getId(),
            [
                'id' => $accountEntity->getId(),
                'channel' => $accountEntity->getChannel(),
                'status' => $accountEntity->getStatus(),
                'stockManagement' => $accountEntity->getStockManagement(),
            ]
        );
        $this->getIntercomEventService()->save($event);
    }

    public function createAction()
    {
        $channel = $this->params()->fromPost('channel');
        $type = $this->params('type');
        $region = $this->params()->fromPost('region');
        $redirectUrl = $this->service->createAccount($type, $channel, $region);

        $view = $this->getJsonModelFactory()->newInstance(['url' => $redirectUrl]);
        return $view;
    }

    public function statusAjaxAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['updated' => false]);

        $accountService = $this->getAccountService();
        try {
            $account = $accountService->fetch(
                $this->params()->fromRoute('account')
            );

            $active = filter_var(
                $this->params()->fromPost('active', $account->getActive()),
                FILTER_VALIDATE_BOOLEAN
            );
            $clearPending = filter_var(
                $this->params()->fromPost('clearPending', !$account->getPending()),
                FILTER_VALIDATE_BOOLEAN
            );

            // If they're trying to enable a pending account force them to enter the credentials
            if ($active && $account->getPending()) {
                $baseRoute = Module::ROUTE . '/' . static::ROUTE . '/' . static::ROUTE_CHANNELS;
                $credentialsUri = $this->accountFactory->createRedirect($account, $baseRoute, ['type' => $account->getType()]);
                $response->setVariable('redirect', $credentialsUri)
                    ->setVariable('updated', true);
                return $response;
            }

            $wasActive = $account->getActive();
            $accountService->save($account->setActive($active)->setPending(!$clearPending));
            $this->notifyOfChange(static::EVENT_ACCOUNT_STATUS_CHANGED, $account);
            $filteredDataTableArray = $this->filterDataTableArrayFields($account);
            $response->setVariable(
                'account',
                $filteredDataTableArray
            );
            if ($wasActive != $active) {
                $channelController = $this->getChannelSpecificController($account);
                if ($channelController && $channelController instanceof AccountActiveToggledInterface) {
                    $channelController->accountActiveToggled($account, $response);
                }
            }
        } catch (NotFound $exception) {
            return $response->setVariable(
                'error',
                'Sales Channel could not be found'
            );
        }

        return $response->setVariable('updated', true);
    }

    public function stockManagementAjaxAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['updated' => false]);
        $account = $this->getAccountService()->fetch($this->params()->fromRoute('account'));
        $stockManagement = filter_var(
            $this->params()->fromPost('active', false),
            FILTER_VALIDATE_BOOLEAN
        );

        $this->getAccountService()->save($account->setStockManagement($stockManagement));
        $this->notifyOfChange(static::EVENT_ACCOUNT_STOCK_MANAGEMENT_CHANGED, $account);
        $filteredDataTableArray = $this->filterDataTableArrayFields($account);
        $response->setVariable(
            'account',
            $filteredDataTableArray
        );
        return $response->setVariable('updated', true);
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

    /**
     * @param AccountEntity $account
     * @return array
     */
    protected function filterDataTableArrayFields(AccountEntity $account)
    {
        $dataTableArray = $this->getMapper()->toDataTableArray($account, $this->url(), $this->params('type'));
        $types = $dataTableArray[static::ACCOUNT_TYPE];
        if ($account->getChannel() == 'amazon' && in_array(Type::SHIPPING, $types)) {
            $key = array_search(Type::SHIPPING, $types);
            unset($dataTableArray[static::ACCOUNT_TYPE][$key]);
        }
        return $dataTableArray;
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
        return $this;
    }

    public function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    public function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    protected function getIntercomEventService()
    {
        return $this->intercomEventService;
    }

    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    protected function setShippingProviderChannelsRepository(ShippingProviderChannelsRepository $shippingProviderChannelsRepository)
    {
        $this->shippingProviderChannelsRepository = $shippingProviderChannelsRepository;
        return $this;
    }

    protected function setAccountFactory(AccountFactory $accountFactory)
    {
        $this->accountFactory = $accountFactory;
        return $this;
    }

    /**
     * To satisfy GetNamespacePartForAccountTrait
     * @return ShippingProviderChannelsRepository
     */
    protected function getShippingProviderChannelsRepository()
    {
        return $this->shippingProviderChannelsRepository;
    }
}
