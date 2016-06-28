<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Module;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Company\Service as CompanyService;
use Zend\Mvc\Controller\AbstractActionController;

class RoyalMailController extends AbstractActionController
{
    const ROUTE_ROYAL_MAIL = 'Royal Mail';

    /** @var SetupService */
    protected $setupService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var CompanyService */
    protected $companyService;

    public function __construct(
        SetupService $setupService,
        ViewModelFactory $viewModelFactory,
        CompanyService $companyService
    ) {
        $this->setSetupService($setupService)
            ->setViewModelFactory($viewModelFactory)
            ->setCompanyService($companyService);
    }

    public function indexAction()
    {
        $rootOuId = $this->setupService->getActiveRootOuId();
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('cg_netdespatch/setup_form/new')
            ->setVariable('ou', $this->companyService->fetchOrganisationUnit($rootOuId))
            ->setVariable('pickUri', $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_ROYAL_MAIL));

        return $this->setupService->getSetupView('Add Royal Mail Shipping', $view, $this->getMainFooterView());
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

    protected function setSetupService(SetupService $setupService)
    {
        $this->setupService = $setupService;
        return $this;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setCompanyService(CompanyService $companyService)
    {
        $this->companyService = $companyService;
        return $this;
    }
}