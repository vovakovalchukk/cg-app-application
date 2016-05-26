<?php
namespace SetupWizard;

use CG\Intercom\Event\Service as EventService;
use CG\Intercom\Event\Request as Event;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;

class StepStatusService implements LoggerAwareInterface
{
    use LogTrait;

    const EVENT_NAME_PREFIX = 'Setup ';
    const LOG_CODE = 'SetupStepStatus';
    const LOG_STATUS = 'User %d (OU %d) %s setup step \'%s\'';

    /** @var EventService */
    protected $eventService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(EventService $eventService, ActiveUserInterface $activeUserContainer)
    {
        $this->setEventService($eventService)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function __invoke($step, $status)
    {
        $eventName = static::EVENT_NAME_PREFIX . $step;
        $user = $this->activeUserContainer->getActiveUser();
        $userId = $user->getId();
        $metaData = ['status' => $status];
        $this->logInfo(static::LOG_STATUS, [$userId, $user->getOrganisationUnitId(), $status, $step], static::LOG_CODE);

        $event = new Event($eventName, $userId, $metaData);
        $this->eventService->save($event);
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
}
