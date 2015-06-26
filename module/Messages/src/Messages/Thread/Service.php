<?php
namespace Messages\Thread;

use CG\Communication\Thread\Collection as ThreadCollection;
use CG\Communication\Thread\Filter as ThreadFilter;
use CG\Communication\Thread\Service as ThreadService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\OrganisationUnit\Service as UserOuService;

class Service
{
    const DEFAULT_LIMIT = 50;
    const ASSIGNEE_ACTIVE_USER = 'active-user';
    const ASSIGNEE_ASSIGNED = 'assigned';
    const ASSIGNEE_UNASSIGNED = 'unassigned';

    protected $threadService;
    protected $userOuService;

    protected $assigneeMethodMap = [
        self::ASSIGNEE_ACTIVE_USER => 'filterByActiveUser',
        self::ASSIGNEE_ASSIGNED => 'filterByAssigned',
        self::ASSIGNEE_UNASSIGNED => 'filterByUnassigned',
    ];

    public function __construct(ThreadService $threadService, UserOuService $userOuService)
    {
        $this->setThreadService($threadService)
            ->setUserOuService($userOuService);
    }

    public function fetchThreadDataForFilters(array $filters)
    {
        $ou = $this->userOuService->getRootOuByActiveUser();

        $threadFilter = new ThreadFilter();
        $threadFilter->setPage(1)
            ->setLimit(isset($filters['limit']) ? $filters['limit'] : static::DEFAULT_LIMIT)
            ->setOrganisationUnitId([$ou->getId()]);
        if (isset($filters['status'])) {
            $threadFilter->setStatus((array)$filters['status']);
        }
        if (isset($filters['assignee'])) {
            $this->filterByAssignee($threadFilter, $filters['assignee']);
        }

        try {
            $threads = $this->threadService->fetchCollectionByFilter($threadFilter);
            return $this->convertThreadCollectionToArray($threads);
        } catch (Notfound $e) {
            return [];
        }
    }

    protected function filterByAssignee(ThreadFilter $threadFilter, $assignee)
    {
        $assignee = strtolower($assignee);
        if (!isset($this->assigneeMethodMap[$assignee])) {
            throw new \UnexpectedValueException(__METHOD__.' was passed unhandled assignee "' . $assignee . '"');
        }
        $method = $this->assigneeMethodMap[$assignee];
        $this->$method($threadFilter);
        return $this;
    }

    protected function convertThreadCollectionToArray(ThreadCollection $threads)
    {
        // We may want to do some manipulation here but this will do for now
        // Deliberately not including the Messages at this stage, we'll load those when an indivual thread is requested
        return $threads->toArray();
    }

    protected function filterByActiveUser(ThreadFilter $threadFilter)
    {
        $user = $this->userOuService->getActiveUser();
        $threadFilter->setAssignedUserId([$user->getId()]);
        return $this;
    }

    protected function filterByAssigned(ThreadFilter $threadFilter)
    {
        //$threadFilter->setIsAssigned(true); // Not available yet, needs CGIV-4698
        return $this;
    }

    protected function filterByUnassigned(ThreadFilter $threadFilter)
    {
        //$threadFilter->setIsAssigned(false); // Not available yet, needs CGIV-4698
        return $this;
    }

    public function fetchThreadDataForId($id)
    {
        $thread = $this->threadService->fetch($id);
        $threadData = $thread->toArray();
        $threadData['messages'] = [];
        foreach ($thread->getMessages() as $message) {
            $threadData['messages'][] = $message->toArray();
        }
        return $threadData;
    }

    protected function setThreadService(ThreadService $threadService)
    {
        $this->threadService = $threadService;
        return $this;
    }

    public function setUserOuService(UserOuService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }
}
