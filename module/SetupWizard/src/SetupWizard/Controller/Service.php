<?php
namespace SetupWizard\Controller;

use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Helper\NavigationMenu;
use CG\User\ActiveUserInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG\Email\Mailer;
use Zend\View\Model\ViewModel;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use LogicException;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const TEMPLATE_EMAIL_CHANNEL_ADD_NOTIFY_CG = 'orderhub/email_channel_add_notify_cg';

    const LOG_CODE = 'SetupWizardControllerService';
    const LOG_CODE_SEND_EMAIL_TO_CG = 'SendEmailToCG';
    const LOG_MSG_SEND_EMAIL_TO_CG = 'Sending email to CG with these details: User: %d, Channel: %s, Subject: %s';
    const LOG_MSG_SENT_EMAIL_TO_CG = 'Sent email to CG';
    const LOG_MSG_SEND_EMAIL_ERROR_NO_TO = 'Failed to send email to CG, there was no-one specified to send the email to';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var NavigationMenu */
    protected $navigationMenu;
    /** @var ServiceLocatorInterface */
    protected $serviceLocator;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var UserOrganisationUnitService */
    protected $userOrganisationUnitService;
    /** @var Mailer $mailer */
    protected $mailer;
    /** @var ViewModel $cgEmailView */
    protected $cgEmailView;
    /** @var mixed $cgEmails */
    protected $cgEmails;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        NavigationMenu $navigationMenu,
        ServiceLocatorInterface $serviceLocator,
        ActiveUserInterface $activeUserContainer,
        OrganisationUnitService $organisationUnitService,
        UserOrganisationUnitService $userOrganisationUnitService,
        Mailer $mailer,
        ViewModel $cgEmailView,
        $cgEmails
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setNavigationMenu($navigationMenu)
            ->setServiceLocator($serviceLocator)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrganisationUnitService($organisationUnitService);
        $this->userOrganisationUnitService = $userOrganisationUnitService;
        $this->mailer = $mailer;
        $this->cgEmailView = $cgEmailView;
        $this->cgEmails = $cgEmails;
    }

    public function sendChannelAddNotificationEmailToCG(string $channel, string $channelPrintName)
    {
        $activeUser = $this->userOrganisationUnitService->getActiveUser();
        $subject = sprintf('User %d tried to connect to %s webstore', $activeUser->getId(), $channelPrintName);
        $this->logDebug(static::LOG_MSG_SEND_EMAIL_TO_CG, ['user' => $activeUser->getId(), 'channel' => $channelPrintName, 'subject' => $subject], [static::LOG_CODE, static::LOG_CODE_SEND_EMAIL_TO_CG]);
        $to = array_filter($this->cgEmails);
        if (!$to || count($to) === 0) {
            $this->logError(static::LOG_MSG_SEND_EMAIL_ERROR_NO_TO, [], [static::LOG_CODE, static::LOG_CODE_SEND_EMAIL_TO_CG]);
            return;
        }
        $view = $this->setUpChannelAddNotificationEmailToCGView($activeUser->getId(), $channelPrintName);
        $this->mailer->send($to, $subject, $view);
        $this->logDebug(static::LOG_MSG_SENT_EMAIL_TO_CG, [], [static::LOG_CODE, static::LOG_CODE_SEND_EMAIL_TO_CG]);
        return $this;
    }

    protected function setUpChannelAddNotificationEmailToCGView(string $userId, string $channelPrintName)
    {
        $view = $this->cgEmailView;
        $view->setTemplate(static::TEMPLATE_EMAIL_CHANNEL_ADD_NOTIFY_CG);
        $view->setVariable('userId', $userId);
        $view->setVariable('channelPrintName', $channelPrintName);
        return $view;
    }

    public function getSetupView($heading, $body, $footer = null)
    {
        $view = $this->getSetupLayoutView();
        if ($heading instanceof ViewModel) {
            $view->addChild($heading, 'heading');
        } else {
            $view->setVariable('heading', $heading);
        }
        if ($body instanceof ViewModel) {
            $view->addChild($body, 'body');
        } else {
            $view->setVariable('body', $body);
        }
        if ($footer === null) {
            $footer = $this->getSetupFooterView();
        }
        if ($footer instanceof ViewModel) {
            $view->addChild($footer, 'footer');
        } elseif ($footer !== false) {
            $view->setVariable('footer', $footer);
        }
        return $view;
    }

    protected function getSetupLayoutView()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/layout/layout')
            ->setVariable('isNavBarVisible', false)
            ->setVariable('isHeaderBarVisible', false);

        $routeParts = explode('/', $this->getCurrentRoute());
        $stepName = array_pop($routeParts);
        $view->setVariable('stepName', $stepName);

        return $view;
    }

    protected function getSetupFooterView()
    {
        $nextStepUri = $this->getNextStepUri();
        if (!$nextStepUri) {
            return false;
        }
        $footer = $this->viewModelFactory->newInstance([
            'buttons' => [
                $this->getNextButtonViewConfig(),
                $this->getSkipButtonViewConfig(),
            ]
        ]);
        $footer->setTemplate('elements/buttons.mustache');
        return $footer;
    }

    public function getNextButtonViewConfig()
    {
        $nextStepUri = $this->getNextStepUri();
        if (!$nextStepUri) {
            return null;
        }
        return [
            'value' => 'Next',
            'id' => 'setup-wizard-next-button',
            'class' => 'setup-wizard-next-button',
            'disabled' => false,
            'action' => $nextStepUri,
        ];
    }

    public function getSkipButtonViewConfig()
    {
        $nextStepUri = $this->getNextStepUri();
        if (!$nextStepUri) {
            return null;
        }
        return [
            'value' => 'Skip',
            'id' => 'setup-wizard-skip-button',
            'class' => 'setup-wizard-skip-button',
            'disabled' => false,
            'action' => $nextStepUri,
        ];
    }

    public function getFirstStepUri()
    {
        return $this->navigationMenu->getFirstPageUri();
    }

    public function getNextStepUri()
    {
        $currentPage = $this->navigationMenu->getPageByRoute($this->getCurrentRoute());
        return $this->navigationMenu->getNextPageUri($currentPage);
    }

    protected function getCurrentRoute()
    {
        return $this->serviceLocator
            ->get('Application')
            ->getMvcEvent()
            ->getRouteMatch()
            ->getMatchedRouteName();
    }

    public function getActiveUser()
    {
        return $this->activeUserContainer->getActiveUser();
    }

    public function getActiveRootOuId()
    {
        return $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
    }

    /**
     * @return \CG\OrganisationUnit\Entity
     */
    public function getActiveRootOu()
    {
        return $this->organisationUnitService->fetch($this->getActiveRootOuId());
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }


    protected function setNavigationMenu(NavigationMenu $navigationMenu)
    {
        $navigationMenu->__invoke('setup-navigation');
        $this->navigationMenu = $navigationMenu;
        return $this;
    }

    protected function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }
}
