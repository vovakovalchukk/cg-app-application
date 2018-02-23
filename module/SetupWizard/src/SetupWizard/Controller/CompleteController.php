<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Prototyper\JsonModelFactory;
use SetupWizard\Module;
use Zend\Mvc\Controller\AbstractActionController;

class CompleteController extends AbstractActionController
{
    const ROUTE_COMPLETE = 'Complete';
    const ROUTE_COMPLETE_THANKS = 'CompleteThanks';
    const ROUTE_COMPLETE_AJAX = 'CompleteAjax';
    const BUSINESS_HOURS_START = '09:00:00';
    const BUSINESS_HOURS_END = '16:00:00';

    /** @var Service */
    protected $service;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(
        Service $service,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory
    ) {
        $this->service = $service;
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function indexAction()
    {
        return $this->service->getSetupView($this->getHeader(), $this->getCallback(), $this->getFooterView());
    }

    public function thanksAction()
    {
        return $this->service->getSetupView($this->getHeader(), $this->getThanks(), false);
    }

    public function ajaxAction()
    {
        return $this->jsonModelFactory->newInstance();
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
        $view->setVariable('callLater', $this->getCallLaterUrl());
        $view->setVariable('thanks', $this->getThanksUrl());
        $view->setVariable('ajax', $this->getAjaxUrl());
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

    protected function getCallLaterUrl()
    {
        return 'https://samgilbert.youcanbook.me';
    }

    protected function getThanksUrl()
    {
        return $this->url()->fromRoute(implode('/', [
            Module::ROUTE,
            static::ROUTE_COMPLETE,
            static::ROUTE_COMPLETE_THANKS
        ]));
    }

    protected function getAjaxUrl()
    {
        return $this->url()->fromRoute(implode('/', [
            Module::ROUTE,
            static::ROUTE_COMPLETE,
            static::ROUTE_COMPLETE_AJAX
        ]));
    }

    protected function getFooterView()
    {
        $footer = $this->viewModelFactory->newInstance();
        $footer->setTemplate('setup-wizard/complete/footer');
        return $footer;
    }
}