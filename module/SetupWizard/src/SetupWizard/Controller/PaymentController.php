<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service as SetupService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PaymentController extends AbstractActionController
{
    const ROUTE_PAYMENT = 'Payment';
    const ROUTE_PACKAGE = 'Package';

    /** @var SetupService */
    protected $setupService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(
        Service $setupService,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory
    ) {
        $this->setupService = $setupService;
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function indexAction()
    {
        return $this->setupService->getSetupView('Add Payment Method', $this->getBody(), $this->getFooter());
    }

    protected function getBody(): ViewModel
    {
        return $this->viewModelFactory->newInstance()->setTemplate('setup-wizard/payment/index');
    }

    protected function getFooter(): ViewModel
    {
        return $this->viewModelFactory->newInstance([
            'buttons' => $this->setupService->getNextButtonViewConfig(),
        ])->setTemplate('elements/buttons.mustache');
    }

    public function setPackageAction()
    {
        return $this->jsonModelFactory->newInstance();
    }
}