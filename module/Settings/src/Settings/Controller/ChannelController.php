<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Settings\Channel\Service;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Settings\Module;
use CG\Account\Client\Service as AccountService;
use CG\Stdlib\Exception\Runtime\NotFound;

class ChannelController extends AbstractActionController
{
    protected $service;
    protected $viewModelFactory;
    protected $jsonModelFactory;
    protected $accountService;

    public function __construct(
        Service $service,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        AccountService $accountService
    ) {
        $this
            ->setService($service)
            ->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setAccountService($accountService);
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
        $accountList->getVariable('settings')->setSource(
            $this->url()->fromRoute(Module::ROUTE . '/Sales Channels/ajax')
        );
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