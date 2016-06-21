<?php
namespace SetupWizard;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Event\Service as EventService;
use CG\Intercom\Event\Request as Event;
use CG\Settings\SetupProgress\Entity as SetupProgress;
use CG\Settings\SetupProgress\Mapper as SetupProgressMapper;
use CG\Settings\SetupProgress\Service as SetupProgressService;
use CG\Settings\SetupProgress\Step\Status as StepStatus;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\User\ActiveUserInterface;
use SetupWizard\Module;
use Zend\Config\Config;
use Zend\Session\SessionManager;

class StepStatusService implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    const EVENT_NAME_PREFIX = 'Setup ';
    const LOG_CODE = 'SetupStepStatus';
    const LOG_STATUS = 'User %d (OU %d) %s setup step \'%s\'';
    const LOG_COMPLETE = 'User %d (OU %d) has completed the setup wizard %s';
    const LOG_LAST_STEP = 'User %d (OU %d) was on setup step \'%s\', will redirect';
    const LOG_NO_STEPS = 'User %d (OU %d) has not been through setup yet, will redirect';
    const LOG_WHITELIST = 'User %d (OU %d) hasn\'t completed the setup wizard but the current route is whitelisted, allowing';
    const STAT_NAME = 'setup-wizard.%s.%s';
    const MAX_SAVE_ATTEMPTS = 2;

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

    /** @var SetupProgress */
    protected $setupProgress;

    public function __construct(
        EventService $eventService,
        ActiveUserInterface $activeUserContainer,
        SetupProgressMapper $setupProgressMapper,
        SetupProgressService $setupProgressService,
        SessionManager $sessionManager,
        Config $config
    ) {
        $this->setEventService($eventService)
            ->setActiveUserContainer($activeUserContainer)
            ->setSetupProgressMapper($setupProgressMapper)
            ->setSetupProgressService($setupProgressService)
            ->setSessionManager($sessionManager)
            ->setConfig($config);
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
        $user = $this->activeUserContainer->getActiveUser();
        $userId = $user->getId();
        $this->logInfo(static::LOG_STATUS, ['user' => $userId, 'ou' => $user->getOrganisationUnitId(), 'setupWizardStepStatus' => $status, 'setupWizardStep' => $step], static::LOG_CODE);
        $this->statsIncrement(sprintf(static::STAT_NAME, $step, $status));

        $this->saveSetupStepProgress($step, $status)
            ->notifyIntercom($step, $status, $userId);
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

        return $this;
    }

    protected function notifyIntercom($step, $status, $userId)
    {
        $eventName = static::EVENT_NAME_PREFIX . $step;
        $metaData = ['status' => $status];

        $event = new Event($eventName, $userId, $metaData);
        $this->eventService->save($event);

        return $this;
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
            $this->logDebug(static::LOG_COMPLETE, ['user' => $user->getId(), 'ou' => $ouId, '(cached)'], [static::LOG_CODE, 'Complete', 'Cached']);
            return true;
        }

        $setupProgress = $this->fetchSetupProgress($ouId);
        if ($setupProgress->isComplete()) {
            if (!isset($session['setup'])) {
                $session['setup'] = [];
            }
            $session['setup']['complete'] = true;
            $this->logDebug(static::LOG_COMPLETE, ['user' => $user->getId(), 'ou' => $ouId, ''], [static::LOG_CODE, 'Complete']);
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

    protected function setEventService(EventService $eventService)
    {
        $this->eventService = $eventService;
        return $this;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setSetupProgressMapper(SetupProgressMapper $setupProgressMapper)
    {
        $this->setupProgressMapper = $setupProgressMapper;
        return $this;
    }

    protected function setSetupProgressService(SetupProgressService $setupProgressService)
    {
        $this->setupProgressService = $setupProgressService;
        return $this;
    }

    protected function setSessionManager(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
        return $this;
    }

    protected function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }
}