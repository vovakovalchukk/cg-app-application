<?php
namespace SetupWizard\Controller;

use CG\Locale\CountryNameByCode;
use CG\Locale\UserLocaleInterface as UserLocale;
use CG_Register\Company\Service as RegisterCompanyService;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service as SetupService;
use Zend\Form\Form;
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
        $detailsFormView = $this->registerCompanyService->getLegalCompanyDetailsForBillingView(false, true);

        /** @var Form $detailsForm */
        $detailsForm = $detailsFormView->getChildrenByCaptureTo('form')[0]->getVariable('form');
        $detailsForm->get('address')->get('country')->setValue(CountryNameByCode::getCountryNameFromCode('US'));
        if ($detailsForm->has('locale')) {
            $detailsForm->get('locale')->setValue(UserLocale::LOCALE_US);
        }

        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/company/index')->addChild($detailsFormView, 'detailsForm');

        return $this->setupService->getSetupView('Company Details', $view, $this->getFooter());
    }

    protected function getFooter(): ViewModel
    {
        return $this->viewModelFactory->newInstance([
            'buttons' => $this->setupService->getNextButtonViewConfig(),
        ])->setTemplate('elements/buttons.mustache');
    }
}