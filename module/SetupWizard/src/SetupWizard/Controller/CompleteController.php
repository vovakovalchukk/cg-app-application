<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class CompleteController extends AbstractActionController
{
    const ROUTE_COMPLETE = 'Complete';
    const BUSINESS_HOURS_START = '09:00:00';
    const BUSINESS_HOURS_END = '16:00:00';

    /** @var Service */
    protected $service;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(Service $service, ViewModelFactory $viewModelFactory)
    {
        $this->service = $service;
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction()
    {
        if ($this->params()->fromQuery('thanks')) {
            return $this->service->getSetupView($this->getHeader(), $this->getThanks(), false);
        }
        return $this->service->getSetupView($this->getHeader(), $this->getCallback(), $this->getFooterView());
    }

    protected function getHeader()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/complete/header');
        return $view;
    }

    protected function getThanks()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/complete/thanks');
        return $view;
    }

    protected function getCallback()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/complete/callback');
        $view->setVariable('callNow', $this->canCallNow());
        return $view;
    }

    protected function canCallNow(\DateTime $now = null): bool
    {
        $now = $now ?? new \DateTime();
        if ($now < new \DateTime(static::BUSINESS_HOURS_START)) {
            return false;
        }
        if ($now > new \DateTime(static::BUSINESS_HOURS_END)) {
            return false;
        }
        return true;
    }

    protected function getFooterView()
    {
        $footer = $this->viewModelFactory->newInstance();
        $footer->setTemplate('setup-wizard/complete/footer');
        return $footer;
    }
}