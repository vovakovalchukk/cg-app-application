<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\View\Model\ViewModel;

class ChannelController extends AbstractActionController
{
    protected $viewModelFactory;

    public function __construct(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
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
        return $list;
    }

    protected function getRouteName()
    {
        $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $routeParts = explode('/', $route);
        return end($routeParts);
    }
}