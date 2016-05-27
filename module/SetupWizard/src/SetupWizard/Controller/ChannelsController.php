<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service;
use Zend\Mvc\Controller\AbstractActionController;

class ChannelsController extends AbstractActionController
{
    const ROUTE_CHANNELS = 'Channels';

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
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/channels/index');

        return $this->service->getSetupView('Add Channels', $view);
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