<?php
namespace SetupWizard\Controller;

use CG\Locale\DemoLink;
use CG\Locale\PhoneNumber;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service as SetupService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DemoController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_DEMO = 'Demonstration';

    /** @var SetupService */
    protected $setupService;
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(
        Service $setupService,
        ActiveUserInterface $activeUser,
        ViewModelFactory $viewModelFactory
    ) {
        $this->setupService = $setupService;
        $this->activeUser = $activeUser;
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction()
    {
        return $this->setupService->getSetupView('Demonstration', $this->getBody(), $this->getFooter());
    }

    protected function getBody(): ViewModel
    {
        $locale = $this->activeUser->getLocale();
        return $this->viewModelFactory->newInstance()
            ->setTemplate('setup-wizard/demo/index')
            ->setVariable('locale', $locale)
            ->setVariable('phoneNumber', PhoneNumber::getForLocale($locale))
            ->setVariable('demoLink', DemoLink::getForLocale($locale));
    }

    protected function getFooter(): ViewModel
    {
        // To disable the Skip/Next buttons, we return a simple view model
        return $this->viewModelFactory->newInstance()->setTemplate('setup-wizard/demo/footer');
    }
}