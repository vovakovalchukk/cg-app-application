<?php
namespace SetupWizard;

use CG\Billing\Subscription\Service as SubscriptionService;
use CG\Email\Mailer;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Event\Request as Event;
use CG\Intercom\Event\Service as EventService;
use CG\OrganisationUnit\Entity as OrganisationUnitEntity;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG\Settings\SetupProgress\Entity as SetupProgress;
use CG\Settings\SetupProgress\Mapper as SetupProgressMapper;
use CG\Settings\SetupProgress\Service as SetupProgressService;
use CG\Settings\SetupProgress\Step\Status as StepStatus;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\DateTime;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\ValidationException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\User\Entity as UserEntity;
use Zend\Config\Config;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use LogicException;

class StepStatusService implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    const EVENT_NAME_PREFIX = 'Setup ';
    const STAT_NAME = 'setup-wizard.%s.%s';
    const MAX_SAVE_ATTEMPTS = 2;
    const TEMPLATE_EMAIL_CHANNEL_ADD_NOTIFY_CG = 'orderhub/email_channel_add_notify_cg';

    const LOG_CODE = 'SetupStepStatus';
    const LOG_STATUS = 'User %d (OU %d) %s setup step \'%s\'';
    const LOG_LAST_STEP = 'User %d (OU %d) was on setup step \'%s\', will redirect';
    const LOG_NO_STEPS = 'User %d (OU %d) has not been through setup yet, will redirect';
    const LOG_WHITELIST = 'User %d (OU %d) hasn\'t completed the setup wizard but the current route is whitelisted, allowing';
    const LOG_TRIAL_END_DATE = 'User %d (OU %d) has completed the Setup Wizard. Their free trial now starts proper and will end in %d days';
    const LOG_CODE_SEND_EMAIL_TO_CG = 'SendEmailToCG';
    const LOG_MSG_SEND_EMAIL_TO_CG = 'Sending email to CG with these details: User: %d, Channel: %s, Message: %s';
    const LOG_MSG_SENT_EMAIL_TO_CG = 'Sent email to CG';
    const LOG_MSG_SEND_EMAIL_ERROR_NO_TO = 'Failed to send email to CG, there was no-one specified to send the email to';

    /** @var EventService */
    protected $eventService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var SetupProgressMapper */
    protected $setupProgressMapper;
    /** @var SetupProgressService */
    protected $setupProgressService;
    /** @var SessionManager */
    protected $sessionManager;
    /** @var Config */
    protected $config;
    /** @var SubscriptionService */
    protected $subscriptionService;
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
    /** @var SetupProgress */
    protected $setupProgress;

    public function __construct(
        EventService $eventService,
        ActiveUserInterface $activeUserContainer,
        SetupProgressMapper $setupProgressMapper,
        SetupProgressService $setupProgressService,
        SessionManager $sessionManager,
        Config $config,
        SubscriptionService $subscriptionService,
        OrganisationUnitService $organisationUnitService,
        UserOrganisationUnitService $userOrganisationUnitService,
        Mailer $mailer,
        ViewModel $cgEmailView,
        $cgEmails
    ) {
        $this->eventService = $eventService;
        $this->activeUserContainer = $activeUserContainer;
        $this->setupProgressMapper = $setupProgressMapper;
        $this->setupProgressService = $setupProgressService;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
        $this->subscriptionService = $subscriptionService;
        $this->organisationUnitService = $organisationUnitService;
        $this->userOrganisationUnitService = $userOrganisationUnitService;
        $this->mailer = $mailer;
        $this->cgEmailView = $cgEmailView;
        $this->cgEmails = $cgEmails;
    }

    public function processStepStatus($previousStep, $previousStepStatus, $currentStep)
    {
        if ($previousStep) {
            $this->processPreviousStep($previousStep, $previousStepStatus);
        }
        if ($currentStep) {
            $this->processCurrentStep($currentStep);
        }
    }

    protected function processCurrentStep($currentStep)
    {
        $this->processStep($currentStep, StepStatus::STARTED);
        return $this;
    }

    protected function processPreviousStep($previousStep, $previousStepStatus)
    {
        $this->processStep($previousStep, $previousStepStatus);
        return $this;
    }

    protected function processStep($step, $status)
    {
        /** @var UserEntity $user */
        $user = $this->activeUserContainer->getActiveUser();
        $userId = $user->getId();
        $this->logInfo(static::LOG_STATUS, ['user' => $userId, 'ou' => $user->getOrganisationUnitId(), 'setupWizardStepStatus' => $status, 'setupWizardStep' => $step], static::LOG_CODE);
        $this->statsIncrement(sprintf(static::STAT_NAME, $step, $status));

        $setupProgress = $this->saveSetupStepProgress($step, $status);
        $this->notifyIntercom($step, $status, $userId);

        // When the wizard is completed start the timer on the free trial
        if ($setupProgress->isComplete()) {
            $this->setFreeTrialEndDate();
            // Also, save the OU with the setup complete date
            $this->saveOrganisationUnit($user);
        }
        return $this;
    }

    protected function saveSetupStepProgress($stepName, $status, $attempt = 1)
    {
        $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $setupProgress = $this->fetchSetupProgress($ouId, true);
        $step = $this->setupProgressMapper->stepFromArray([
            'name' => $stepName,
            'status' => $status,
            'modified' => (new StdlibDateTime())->stdFormat(),
        ]);
        $setupProgress->addStep($step);

        try {
            $this->setupProgressService->save($setupProgress);
        } catch (NotModified $e) {
            // No-op
        } catch (Conflict $e) {
            if ($attempt >= static::MAX_SAVE_ATTEMPTS) {
                throw $e;
            }
            return $this->saveSetupStepProgress($stepName, $status, ++$attempt);
        }

        return $setupProgress;
    }

    public function sendChannelAddNotificationEmailToCG(string $channel, string $channelPrintName, string $message)
    {
        $activeUser = $this->userOrganisationUnitService->getActiveUser();
        $email = $message;
        $this->logDebug(static::LOG_MSG_SEND_EMAIL_TO_CG, ['user' => $activeUser->getId(), 'channel' => $channel, 'channelPrintName' => $channelPrintName, 'message' => $message], [static::LOG_CODE, static::LOG_CODE_SEND_EMAIL_TO_CG]);
        $to = array_filter($this->cgEmails);
        if (!$to || count($to) === 0) {
            $this->logError(static::LOG_MSG_SEND_EMAIL_ERROR_NO_TO, [], [static::LOG_CODE, static::LOG_CODE_SEND_EMAIL_TO_CG]);
            throw new LogicException('No CG emails configured in the StepStatusService');
        }
        $subject = $message;
        $view = $this->setUpChannelAddNotificationEmailToCGView($activeUser->getId(), $channelPrintName, $email);
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

    protected function notifyIntercom($step, $status, $userId)
    {
        $eventName = static::EVENT_NAME_PREFIX . $step;
        $metaData = ['status' => $status];

        $event = new Event($eventName, $userId, $metaData);
        $this->eventService->save($event);

        return $this;
    }

    protected function setFreeTrialEndDate()
    {
        $user = $this->activeUserContainer->getActiveUser();
        $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $freeTrialDays = SubscriptionService::FREE_TRIAL_DAYS;
        $subscription = $this->subscriptionService->fetchActiveSubscriptionForOuId($ouId);

        // If they already have an end date we don't want to extend it any further
        if ($subscription->getToDate() !== null) {
            return;
        }

        try {
            $this->subscriptionService->extendTrial($subscription, $freeTrialDays);
            $this->logDebug(static::LOG_TRIAL_END_DATE, [$user->getId(), $ouId, $freeTrialDays], [static::LOG_CODE, 'TrialEndDate']);

        } catch (ValidationException $e) {
            // This would be thrown if the user is not on a free trial. No-op.
        }
    }

    public function getRedirectRouteIfIncomplete($currentRoute)
    {
        $user = $this->activeUserContainer->getActiveUser();
        if (!$user) {
            return null;
        }
        $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();

        if ($this->isSetupComplete($user, $ouId)) {
            return null;
        }
        if ($this->isWhiteListedRoute($currentRoute, $user, $ouId)) {
            return null;
        }

        $setupProgress = $this->fetchSetupProgress($ouId);
        $lastStep = $setupProgress->getLastStep();
        if (!$lastStep) {
            $this->logDebug(static::LOG_NO_STEPS, ['user' => $user->getId(), 'ou' => $ouId], [static::LOG_CODE, 'NoSteps']);
            return Module::ROUTE;
        }

        $this->logDebug(static::LOG_LAST_STEP, ['user' => $user->getId(), 'ou' => $ouId, 'setupWizardStep' => $lastStep->getName()], [static::LOG_CODE, 'LastStep']);
        return Module::ROUTE . '/' . $lastStep->getName();
    }

    protected function isSetupComplete($user, $ouId)
    {
        $session = $this->sessionManager->getStorage();
        if (isset($session['setup'], $session['setup']['complete']) && $session['setup']['complete'] == true) {
            // Don't log anything here as it gets very spammy
            return true;
        }

        $setupProgress = $this->fetchSetupProgress($ouId);
        if ($setupProgress->isComplete()) {
            if (!isset($session['setup'])) {
                $session['setup'] = [];
            }
            $session['setup']['complete'] = true;
            return true;
        }

        return false;
    }

    protected function fetchSetupProgress($id, $force = false)
    {
        if (!$this->setupProgress || $force) {
            $this->setupProgress = $this->setupProgressService->fetch($id);
        }
        return $this->setupProgress;
    }

    protected function isWhiteListedRoute($route, $user, $ouId)
    {
        $config = $this->config->get('SetupWizard')->get('SetupWizard');
        $whitelist = $config->get('white_listed_routes');
        if (isset($whitelist[$route])) {
            $this->logDebug(static::LOG_WHITELIST, ['user' => $user->getId(), 'ou' => $ouId], [static::LOG_CODE, 'Whitelist']);
            return true;
        }
        return false;
    }

    protected function saveOrganisationUnit(UserEntity $user)
    {
        /** @var OrganisationUnitEntity $ou */
        $ou = $this->organisationUnitService->fetch($user->getOrganisationUnitId());
        $ou->getMetaData()->setSetupCompleteDate((new DateTime())->format(DateTime::FORMAT));
        $this->organisationUnitService->save($ou);
    }
}
