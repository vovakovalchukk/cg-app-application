<?php
namespace SetupWizard\Controller;

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

    public function __construct(Service $service, ViewModelFactory $viewModelFactory)
    {
        $this->setService($service)
            ->setViewModelFactory($viewModelFactory);
    }

    public function indexAction()
    {
        $this->redirect()->toUrl($this->service->getFirstStepUri());
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
}