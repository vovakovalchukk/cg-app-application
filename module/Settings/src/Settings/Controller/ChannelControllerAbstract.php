<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG\Account\CreationServiceAbstract as AccountCreationService;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;

abstract class ChannelControllerAbstract extends AbstractActionController
{
    protected $accountCreationService;
    protected $activeUserContainer;
    protected $jsonModelFactory;
    protected $viewModelFactory;

    public function __construct(
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory
    ) {
        $this->setAccountCreationService($accountCreationService)
            ->setActiveUserContainer($activeUserContainer)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory);
    }

    protected function setAccountCreationService(AccountCreationService $accountCreationService)
    {
        $this->accountCreationService = $accountCreationService;
        return $this;
    }

    protected function getAccountCreationService()
    {
        return $this->accountCreationService;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }
}