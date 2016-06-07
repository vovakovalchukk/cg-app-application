<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Company\Service as CompanyService;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Module;
use Zend\Mvc\Controller\AbstractActionController;

class CompanyController extends AbstractActionController
{
    const ROUTE_COMPANY = 'Company';
    const ROUTE_COMPANY_SAVE = 'Save';

    /** @var SetupService */
    protected $setupService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var CompanyService */
    protected $companyService;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(
        SetupService $setupService,
        ViewModelFactory $viewModelFactory,
        CompanyService $companyService,
        JsonModelFactory $jsonModelFactory
    ) {
        $this->setSetupService($setupService)
            ->setViewModelFactory($viewModelFactory)
            ->setCompanyService($companyService)
            ->setJsonModelFactory($jsonModelFactory);
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/company/index')
            ->setVariable('saveUri', $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_COMPANY . '/' . static::ROUTE_COMPANY_SAVE));

        return $this->setupService->getSetupView('Company Details', $view);
    }

    public function saveAction()
    {
        $rootOuId = $this->setupService->getActiveRootOuId();
        $details = $this->params()->fromPost();
        $this->companyService->saveCompanyDetails($rootOuId, $details);

        return $this->jsonModelFactory->newInstance(['success' => true]);
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

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }
}