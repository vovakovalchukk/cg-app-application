<?php
namespace SetupWizard;

use CG\Billing\Subscription\Service as SubscriptionService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Event\Request as Event;
use CG\Intercom\Event\Service as EventService;
use CG\OrganisationUnit\Entity as OrganisationUnitEntity;
use CG\OrganisationUnit\Service as OrganisationUnitService;
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

class StepStatusService implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    const EVENT_NAME_PREFIX = 'Setup ';
    const STAT_NAME = 'setup-wizard.%s.%s';
    const MAX_SAVE_ATTEMPTS = 2;

    const LOG_CODE = 'SetupStepStatus';
    const LOG_STATUS = 'User %d (OU %d) %s setup step \'%s\'';
    const LOG_LAST_STEP = 'User %d (OU %d) was on setup step \'%s\', will redirect';
    const LOG_NO_STEPS = 'User %d (OU %d) has not been through setup yet, will redirect';
    const LOG_WHITELIST = 'User %d (OU %d) hasn\'t completed the setup wizard but the current route is whitelisted, allowing';
    const LOG_TRIAL_END_DATE = 'User %d (OU %d) has completed the Setup Wizard. Their free trial now starts proper and will end in %d days';

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
        OrganisationUnitService $organisationUnitService
    ) {
        $this->eventService = $eventService;
        $this->activeUserContainer = $activeUserContainer;
        $this->setupProgressMapper = $setupProgressMapper;
        $this->setupProgressService = $setupProgressService;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
        $this->subscriptionService = $subscriptionService;
        $this->organisationUnitService = $organisationUnitService;
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
        /** @var OrganisationUnitEntity $rootOu */
        $rootOu = $this->organisationUnitService->fetch(
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
        );
        if ($rootOu->getBillingType() !== OrganisationUnitEntity::BILLING_TYPE_CG) {
            return;
        }
        $freeTrialDays = SubscriptionService::FREE_TRIAL_DAYS;
        $subscription = $this->subscriptionService->fetchActiveSubscriptionForOuId($rootOu->getId());

        // If they already have an end date we don't want to extend it any further
        if ($subscription->getToDate() !== null) {
            return;
        }

        try {
            $this->subscriptionService->extendTrial($subscription, $freeTrialDays);
            $this->logDebug(static::LOG_TRIAL_END_DATE, [$user->getId(), $rootOu->getId(), $freeTrialDays], [static::LOG_CODE, 'TrialEndDate']);

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
