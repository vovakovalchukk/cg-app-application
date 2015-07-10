<?php
namespace Messages\Thread;

use CG\Communication\Thread\Collection as ThreadCollection;
use CG\Communication\Thread\Filter as ThreadFilter;
use CG\Communication\Thread\Service as ThreadService;
use CG\Communication\Thread\Status as ThreadStatus;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\OrganisationUnit\Service as UserOuService;
use Zend\Navigation\Page\AbstractPage as NavPage;

class Service
{
    const DEFAULT_LIMIT = 50;
    const KEY_HAS_NEW = 'messages-has-new';
    const TTL_HAS_NEW = 300;
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return bool
     */
    public function hasNew()
    {
        $success = false;
        $cachedValue = apc_fetch(static::KEY_HAS_NEW, $success);
        if ($success) {
            return $cachedValue;
        }

        $ou = $this->userOuService->getRootOuByActiveUser();
        $hasNew = ($this->hasNewUnassigned($ou) || $this->hasNewAssignedToActiveUser($ou));
        apc_store(static::KEY_HAS_NEW, $hasNew, static::TTL_HAS_NEW);
        return $hasNew;
    }

    protected function hasNewUnassigned($ou)
    {
        $threadFilter = new ThreadFilter();
        $threadFilter->setPage(1)
            ->setLimit(1)
            ->setOrganisationUnitId([$ou->getId()])
            ->setStatus([ThreadStatus::NEW_THREAD])
            ->setIsAssigned(false);
        try {
            $this->threadService->fetchCollectionByFilter($threadFilter);
            return true;
        } catch (Notfound $e) {
            return false;
        }
    }

    protected function hasNewAssignedToActiveUser($ou)
    {
        $user = $this->userOuService->getActiveUser();
        $threadFilter = new ThreadFilter();
        $threadFilter->setPage(1)
            ->setLimit(1)
            ->setOrganisationUnitId([$ou->getId()])
            ->setStatus([ThreadStatus::NEW_THREAD])
            ->setAssignedUserId([$user->getId()]);
        try {
            $this->threadService->fetchCollectionByFilter($threadFilter);
            return true;
        } catch (Notfound $e) {
            return false;
        }
    }

    public function changeNavSpriteIfHasNew(NavPage $page)
    {
        if (!$this->hasNew()) {
            return;
        }
        $page->set('sprite', 'sprite-messages-alert-18-white');
    }

    protected function setThreadService(ThreadService $threadService)
    {
        $this->threadService = $threadService;
        return $this;
    }

    protected function setUserOuService(UserOuService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }
}
