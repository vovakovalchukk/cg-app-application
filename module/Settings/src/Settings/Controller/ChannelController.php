<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Settings\Channel\Service;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\View\Model\ViewModel;

class ChannelController extends AbstractActionController
{
    const LIST_ROUTE = 'Sales Channels';

    protected $service;
    protected $viewModelFactory;

    public function __construct(Service $service, ViewModelFactory $viewModelFactory)
    {
        $this->setService($service)->setViewModelFactory($viewModelFactory);
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
        return $list;
    }

    protected function getRouteName()
    {
        $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $routeParts = explode('/', $route);
        return end($routeParts);
    }
}