<?php
namespace SetupWizard\Callback;

use CG\Email\Mailer;
use CG\OrganisationUnit\Service as OuService;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\View\Model\ViewModel;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var OuService */
    protected $ouService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var Mailer */
    protected $mailer;
    /** @var mixed */
    protected $notificationEmail;

    public function __construct(
        ActiveUserInterface $activeUser,
        OuService $ouService,
        ViewModelFactory $viewModelFactory,
        Mailer $mailer,
        $notificationEmail = null
    ) {
        $this->activeUser = $activeUser;
        $this->ouService = $ouService;
        $this->viewModelFactory = $viewModelFactory;
        $this->mailer = $mailer;
        $this->notificationEmail = $notificationEmail;
    }

    public function sendCallbackEmail(bool $callNow)
    {
        if (!$this->notificationEmail) {
            return;
        }

        $this->mailer->send(
            $this->notificationEmail,
            sprintf('Setup Wizard - Callback Request (%s: %s)', ENVIRONMENT, $this->getActiveUser()->getRootOuId()),
            $this->getEmailView($callNow)
        );
    }

    protected function getEmailView(bool $callNow): ViewModel
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/email/callback');
        $view->setVariable('user', $this->getActiveUser());
        $view->setVariable('ou', $this->ouService->fetch($this->getActiveUser()->getRootOuId()));
        $view->setVariable('callNow', $callNow);
        return $view;
    }

    protected function getActiveUser(): User
    {
        return $this->activeUser->getActiveUser();
    }
}