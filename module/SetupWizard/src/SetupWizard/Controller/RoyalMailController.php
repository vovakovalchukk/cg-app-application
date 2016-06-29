<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Module;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Company\Service as CompanyService;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Account\Client\Service as AccountService;
use CG_NetDespatch\Account\CreationService as AccountCreationService;

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
        $rootOuId = $this->setupService->getActiveRootOuId();
        $ou = $this->companyService->fetchOrganisationUnit($rootOuId);

        $account = null;
        if ($accountId = $this->params()->fromQuery('accountId')) {
            $account = $this->accountService->fetch($accountId);
        }

        $form = $this->accountCreationService->generateSetupForm($account);

        $saveRoute = implode('/', [Module::ROUTE, static::ROUTE_ROYAL_MAIL]);
        $saveUrl = $this->url()->fromRoute($saveRoute);
        $form->setVariable('saveUrl', $saveUrl);

        $view = $this->viewModelFactory->newInstance()->setTemplate('cg_netdespatch/setup')->addChild($form, 'form');
        $view->setVariables($form->getVariables());

        return $this->setupService->getSetupView('Add Royal Mail Shipping', $view, $this->getMainFooterView());
    }

    protected function getAccountRoute()
    {
        //return implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS]);
    }

    protected function getMainFooterView()
    {
        // No skip button for this step as it must be completed
        $footer = $this->viewModelFactory->newInstance([
            'buttons' => [
                $this->setupService->getNextButtonViewConfig(),
            ]
        ]);
        $footer->setTemplate('elements/buttons.mustache');
        return $footer;
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