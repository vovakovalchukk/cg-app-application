<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Company\Service as CompanyService;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Account\Client\Service as AccountService;
use CG_NetDespatch\Account\CreationService as AccountCreationService;
use CG_NetDespatch\Module as NetdespatchModule;
use CG_NetDespatch\Controller\AccountController;

class RoyalMailController extends AbstractActionController
{
    const ROUTE_ROYAL_MAIL = 'Royal Mail';

    /** @var SetupService */
    protected $setupService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var CompanyService */
    protected $companyService;
    /** @var AccountService */
    protected $accountService;
    /** @var AccountCreationService */
    protected $accountCreationService;

    public function __construct(
        SetupService $setupService,
        ViewModelFactory $viewModelFactory,
        CompanyService $companyService,
        AccountService $accountService,
        AccountCreationService $accountCreationService
    ) {
        $this->setSetupService($setupService)
            ->setViewModelFactory($viewModelFactory)
            ->setCompanyService($companyService)
            ->setAccountService($accountService)
            ->setAccountCreationService($accountCreationService);
    }

    public function indexAction()
    {
        $account = null;
        if ($accountId = $this->params()->fromQuery('accountId')) {
            $account = $this->accountService->fetch($accountId);
        }

        $form = $this->accountCreationService->generateSetupForm($account);
        $saveUrl = $this->url()->fromRoute($this->getAccountRoute());
        $form->setVariable('saveUrl', $saveUrl);
        $formView = $this->viewModelFactory->newInstance()->setTemplate('cg_netdespatch/setup')->addChild($form, 'form');
        $formView->setVariables($form->getVariables());

        $wrapperView = $this->viewModelFactory->newInstance()
            ->setTemplate('setup-wizard/royal-mail/index')
            ->addChild($formView, 'formView');

        return $this->setupService->getSetupView('Add Royal Mail Shipping', $wrapperView);
    }

    protected function getAccountRoute()
    {
        return implode('/', [NetdespatchModule::ROUTE, AccountController::ROUTE, AccountController::ROUTE_SAVE]);
    }

    /**
     * @return self
     */
    protected function setSetupService(SetupService $setupService)
    {
        $this->setupService = $setupService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setCompanyService(CompanyService $companyService)
    {
        $this->companyService = $companyService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setAccountCreationService(AccountCreationService $accountCreationService)
    {
        $this->accountCreationService = $accountCreationService;
        return $this;
    }
}