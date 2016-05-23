<?php
namespace SetupWizard\Controller;

use CG_UI\View\Helper\NavigationMenu;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    const ROUTE_EXAMPLE = 'Example';

    /** @var Service */
    protected $service;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var NavigationMenu */
    protected $navigationMenu;

    public function __construct(Service $service, ViewModelFactory $viewModelFactory, NavigationMenu $navigationMenu)
    {
        $this->setService($service)
            ->setViewModelFactory($viewModelFactory)
            ->setNavigationMenu($navigationMenu);
    }

    public function indexAction()
    {
        $navMenuHelper = $this->navigationMenu->__invoke('setup-navigation');
        $firstStepUri = $navMenuHelper->getFirstPageUri();
        $this->redirect()->toUrl($firstStepUri);
    }

    public function exampleAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/index/example');

        return $this->service->getSetupView('Example heading', $view);
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setNavigationMenu(NavigationMenu $navigationMenu)
    {
        $this->navigationMenu = $navigationMenu;
        return $this;
    }
}