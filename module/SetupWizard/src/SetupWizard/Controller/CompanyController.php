<?php
namespace SetupWizard\Controller;

use CG_Register\Company\Service as RegisterCompanyService;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service as SetupService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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
        Service $setupService,
        ViewModelFactory $viewModelFactory,
        RegisterCompanyService $registerCompanyService
    ) {
        $this->setupService = $setupService;
        $this->viewModelFactory = $viewModelFactory;
        $this->registerCompanyService = $registerCompanyService;
    }

    public function indexAction()
    {
        $detailsForm = $this->registerCompanyService->getLegalCompanyDetailsForBillingView();

        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/company/index')
            ->addChild($detailsForm, 'detailsForm'); 

        return $this->setupService->getSetupView('Company Details', $view, $this->getFooter());
    }

    protected function getFooter(): ViewModel
    {
        return $this->viewModelFactory->newInstance([
            'buttons' => $this->setupService->getNextButtonViewConfig(),
        ])->setTemplate('elements/buttons.mustache');
    }
}