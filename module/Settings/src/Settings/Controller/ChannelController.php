<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Settings\Channel\Service;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\User\ActiveUserInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use CG\User\Entity as User;
use Settings\Module;
use CG\Account\Client\Service as AccountService;
use CG\Stdlib\Exception\Runtime\NotFound;
use DirectoryIterator;

class ChannelController extends AbstractActionController
{
    const LIST_ROUTE = 'Sales Channels';
    const LIST_AJAX_ROUTE = 'ajax';

    protected $service;
    protected $viewModelFactory;
    protected $jsonModelFactory;
    protected $accountService;
    protected $activeUserContainer;

    public function __construct(
        Service $service,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        AccountService $accountService,
        ActiveUserInterface $activeUserContainer
    ) {
        $this
            ->setService($service)
            ->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setAccountService($accountService)
            ->setActiveUserContainer($activeUserContainer);
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
     * @param array|null $variables
     * @param array|null $options
     * @return ViewModel
     */
    protected function newViewModel($variables = null, $options = null)
    {
        return $this->getViewModelFactory()->newInstance($variables, $options);
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    /**
     * @param array|null $variables
     * @param array|null $options
     * @return JsonModel
     */
    protected function newJsonModel($variables = null, $options = null)
    {
        return $this->getJsonModelFactory()->newInstance($variables, $options);
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

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ActiveUserInterface
     */
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
            $this->getAccountList(),
            'accountList'
        );
        return $list;
    }

    protected function getAccountList()
    {
        $accountList = $this->getService()->getAccountList();
        $settings = $accountList->getVariable('settings');

        $settings->setSource(
            $this->url()->fromRoute(Module::ROUTE . '/' . static::LIST_ROUTE . '/' . static::LIST_AJAX_ROUTE)
        );

        $settings->setTemplateUrlMap($this->getAccountListTemplates());

        return $accountList;
    }

    protected function basePath()
    {
        $config = $this->getServiceLocator()->get('Config');
        if (isset($config['view_manager'], $config['view_manager']['base_path'])) {
            return $config['view_manager']['base_path'];
        }
        else {
            return $this->getServiceLocator()->get('Request')->getBasePath();
        }
    }

    protected function getAccountListTemplates()
    {
        $templateUrlMap = [];
        $webRoot = PROJECT_ROOT . '/public';

        $templates = new DirectoryIterator($webRoot . Module::PUBLIC_FOLDER . 'template/columns');
        foreach ($templates as $template) {
            if (!$template->isFile()) {
                continue;
            }
            $templateUrlMap[$template->getBasename('.html')]
                = $this->basePath() . str_replace($webRoot, '', $template->getPathname());
        }

        return $templateUrlMap;
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
                $data['Records'][] = $account->toArray();
            }
        } catch (NotFound $exception) {
            // No accounts so ignoring
        }

        return $this->newJsonModel($data);
    }

    protected function getRouteName()
    {
        $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $routeParts = explode('/', $route);
        return end($routeParts);
    }
}