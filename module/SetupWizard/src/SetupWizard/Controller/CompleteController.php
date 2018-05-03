<?php
namespace SetupWizard\Controller;

use CG\User\ActiveUserInterface as ActiveUser;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class CompleteController extends AbstractActionController
{
    const ROUTE_COMPLETE = 'Complete';

    /** @var Service */
    protected $service;
    /** @var ActiveUser */
    protected $activeUser;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(Service $service, ActiveUser $activeUser, ViewModelFactory $viewModelFactory)
    {
        $this->service = $service;
        $this->activeUser = $activeUser;
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/complete/index');
        $view->setVariable('name', $this->getActiveUsersName());
        return $this->service->getSetupView('Complete', $view, $this->getFooterView());
    }

    protected function getActiveUsersName()
    {
        return $this->service->getActiveUser()->getFirstName();
    }

    protected function getFooterView()
    {
        $nextUri = $this->url()->fromRoute('home');
        $footer = $this->viewModelFactory->newInstance([
            'buttons' => [
                [
                    'value' => 'Done',
                    'id' => 'setup-wizard-done-button',
                    'class' => 'setup-wizard-next-button setup-wizard-done-button',
                    'disabled' => false,
                    'action' => $nextUri,
                ]
            ]
        ]);
        $footer->setTemplate('elements/buttons.mustache');
        return $footer;
    }
}