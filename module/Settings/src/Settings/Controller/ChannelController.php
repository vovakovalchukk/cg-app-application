<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Settings\Channel\Service;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class ChannelController extends AbstractActionController
{
    protected $service;
    protected $viewModelFactory;
    protected $jsonModelFactory;

    public function __construct(
        Service $service,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory
    ) {
        $this->setService($service)->setViewModelFactory($viewModelFactory)->setJsonModelFactory($jsonModelFactory);
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
        return $list;
    }

    public function listAjaxAction()
    {
        return $this->newJsonModel();
    }

    protected function getRouteName()
    {
        $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $routeParts = explode('/', $route);
        return end($routeParts);
    }
}