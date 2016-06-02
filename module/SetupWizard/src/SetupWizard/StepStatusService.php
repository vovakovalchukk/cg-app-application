<?php
namespace SetupWizard;

use CG\Intercom\Event\Service as EventService;
use CG\Intercom\Event\Request as Event;
use CG\Settings\SetupProgress\Mapper as SetupProgressMapper;
use CG\Settings\SetupProgress\Service as SetupProgressService;
use CG\Settings\SetupProgress\Step\Status as StepStatus;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\User\ActiveUserInterface;

class StepStatusService implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    const EVENT_NAME_PREFIX = 'Setup ';
    const LOG_CODE = 'SetupStepStatus';
    const LOG_STATUS = 'User %d (OU %d) %s setup step \'%s\'';
    const STAT_NAME = 'setup-wizard.%s.%s';

    /** @var EventService */
    protected $eventService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var SetupProgressMapper */
    protected $setupProgressMapper;
    /** @var SetupProgressService */
    protected $setupProgressService;

    public function __construct(
        EventService $eventService,
        ActiveUserInterface $activeUserContainer,
        SetupProgressMapper $setupProgressMapper,
        SetupProgressService $setupProgressService
    ) {
        $this->setEventService($eventService)
            ->setActiveUserContainer($activeUserContainer)
            ->setSetupProgressMapper($setupProgressMapper)
            ->setSetupProgressService($setupProgressService);
    }

    public function processStepStatus($currentStep, $previousStep, $previousStepStatus)
    {
        if ($currentStep) {
            $this->processCurrentStep($currentStep);
        }
        if ($previousStep) {
            $this->processPreviousStep($previousStep, $previousStepStatus);
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

    protected function saveSetupStepProgress($stepName, $status)
    {
        $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $setupProgress = $this->setupProgressService->fetch($ouId);
        $step = $this->setupProgressMapper->stepFromArray([
            'name' => $stepName,
            'status' => $status,
            'modified' => (new StdlibDateTime())->stdFormat(),
        ]);
        $setupProgress->addStep($step);
        $this->setupProgressService->save($setupProgress);

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
}
