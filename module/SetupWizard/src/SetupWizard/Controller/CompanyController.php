<?php
namespace SetupWizard\Controller;

use CG_Register\Company\Service as RegisterCompanyService;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service as SetupService;
use Zend\Mvc\Controller\AbstractActionController;

class CompanyController extends AbstractActionController
{
    const ROUTE_COMPANY = 'Company';
    const ROUTE_COMPANY_SAVE = 'Save';

    /** @var SetupService */
    protected $setupService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var RegisterCompanyService */
    protected $registerCompanyService;

    public function __construct(
        SetupService $setupService,
        ViewModelFactory $viewModelFactory,
        RegisterCompanyService $registerCompanyService
    ) {
        $this->setSetupService($setupService)
            ->setViewModelFactory($viewModelFactory)
            ->setRegisterCompanyService($registerCompanyService);
    }

    public function indexAction()
    {
        $detailsForm = $this->registerCompanyService->getLegalCompanyDetailsView();

        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/company/index')
            ->addChild($detailsForm, 'detailsForm'); 

        return $this->setupService->getSetupView('Company Details', $view);
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

    protected function setRegisterCompanyService(RegisterCompanyService $registerCompanyService)
    {
        $this->registerCompanyService = $registerCompanyService;
        return $this;
    }
}