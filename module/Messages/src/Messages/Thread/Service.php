<?php
namespace Messages\Thread;

use CG\Account\Client\Service as AccountService;
use CG\Communication\Thread\Collection as ThreadCollection;
use CG\Communication\Thread\Entity as Thread;
use CG\Communication\Thread\Filter as ThreadFilter;
use CG\Communication\Thread\ResolveFactory as ThreadResolveFactory;
use CG\Communication\Thread\Service as ThreadService;
use CG\Communication\Thread\Status as ThreadStatus;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\User\OrganisationUnit\Service as UserOuService;
use CG\User\Service as UserService;
use Messages\Message\FormatMessageDataTrait;
use Zend\Navigation\Page\AbstractPage as NavPage;

class Service
{
    use FormatMessageDataTrait;

    const DEFAULT_LIMIT = 50;
    const KEY_HAS_NEW = 'messages-has-new';
    const TTL_HAS_NEW = 300;
    const ASSIGNEE_ACTIVE_USER = 'active-user';
    const ASSIGNEE_ASSIGNED = 'assigned';
    const ASSIGNEE_UNASSIGNED = 'unassigned';

    protected $threadService;
    protected $userOuService;
    protected $userService;
    protected $accountService;
    protected $threadResolveFactory;

    protected $assigneeMethodMap = [
        self::ASSIGNEE_ACTIVE_USER => 'filterByActiveUser',
        self::ASSIGNEE_ASSIGNED => 'filterByAssigned',
        self::ASSIGNEE_UNASSIGNED => 'filterByUnassigned',
    ];
    protected $filtersThatIncludeResolved = [
        'status' => 'status',
        'searchTerm' => 'searchTerm',
        'id' => 'id'
    ];
    protected $statusSortOrder = [
        10 => ThreadStatus::NEW_THREAD,
        20 => ThreadStatus::AWAITING_REPLY,
        30 => ThreadStatus::RESOLVED,
    ];

    public function __construct(
        ThreadService $threadService,
        UserOuService $userOuService,
        UserService $userService,
        AccountService $accountService,
        ThreadResolveFactory $threadResolveFactory
    ) {
        $this->setThreadService($threadService)
            ->setUserOuService($userOuService)
            ->setUserService($userService)
            ->setAccountService($accountService)
            ->setThreadResolveFactory($threadResolveFactory);
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
        if (isset($filters['id'])) {
            $threadFilter->setId((array)$filters['id']);
        }
        if (isset($filters['status'])) {
            $threadFilter->setStatus((array)$filters['status']);
        }
        if (isset($filters['assignee'])) {
            $this->filterByAssignee($threadFilter, $filters['assignee']);
        }
        if (isset($filters['searchTerm'])) {
            $threadFilter->setSearchTerm($filters['searchTerm']);
        }
        $excludeResolved = true;
        foreach ($this->filtersThatIncludeResolved as $filterType) {
            if (isset($filters[$filterType])) {
                $excludeResolved = false;
                break;
            }
        }
        if ($excludeResolved) {
            $this->filterByNotResolved($threadFilter);
        }

        try {
            $threads = $this->threadService->fetchCollectionByFilter($threadFilter);
            $sortedThreads = $this->sortThreadCollection($threads);
            return $this->convertThreadCollectionToArray($sortedThreads);
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

    protected function filterByNotResolved(ThreadFilter $threadFilter)
    {
        $otherStatuses = array_diff(ThreadStatus::getStatuses(), [ThreadStatus::RESOLVED]);
        $threadFilter->setStatus($otherStatuses);
        return $this;
    }

    protected function convertThreadCollectionToArray(ThreadCollection $threads)
    {
        $threadsData = [];
        foreach ($threads as $thread) {
            $threadsData[] = $this->formatThreadData($thread);
        }
        return $threadsData;
    }

    protected function formatThreadData(Thread $thread)
    {
        $threadData = $thread->toArray();
        $messages = [];
        foreach ($thread->getMessages() as $message) {
            $messageData = $this->formatMessageData($message, $thread);
            $messages[$message->getCreated()] = $messageData;
        }
        ksort($messages);
        $threadData['messages'] = array_values($messages);

        $account = $this->accountService->fetch($thread->getAccountId());
        $threadData['accountName'] = $account->getDisplayName();

        $created = new StdlibDateTime($threadData['created']);
        $updated = new StdlibDateTime($threadData['updated']);
        $threadData['created'] = $created->uiFormat();
        $threadData['createdFuzzy'] = $created->fuzzyFormat();
        $threadData['updated'] = $updated->uiFormat();
        $threadData['updatedFuzzy'] = $updated->fuzzyFormat();

        $threadData['assignedUserName'] = '';
        if ($threadData['assignedUserId']) {
            $assignedUser = $this->userService->fetch($threadData['assignedUserId']);
            $threadData['assignedUserName'] = $assignedUser->getFirstName() . ' ' . $assignedUser->getLastName();
        }

        return $threadData;
    }

    protected function sortThreadCollection(ThreadCollection $threads)
    {
        // Sort by status then updated date
        $sortedCollection = new ThreadCollection(Thread::class, __FUNCTION__);
        $threadsByStatus = [];
        foreach ($this->statusSortOrder as $status)
        {
            $threadsByStatus[$status] = [];
        }
        foreach ($threads as $thread) {
            // Handle any statuses not in the sort map by just adding them on to the end
            if (!isset($threadsByStatus[$thread->getStatus()])) {
                $threadsByStatus[$thread->getStatus()] = [];
            }
            // Append the ID to the updated date to make it unique but still sortable
            $key = $thread->getUpdated() . ' ' . $thread->getId();
            $threadsByStatus[$thread->getStatus()][$key] = $thread;
        }
        foreach ($threadsByStatus as $status => $threadsByUpdated) {
            ksort($threadsByUpdated);
            foreach ($threadsByUpdated as $thread) {
                $sortedCollection->attach($thread);
            }
        }
        return $sortedCollection;
    }

    protected function filterByActiveUser(ThreadFilter $threadFilter)
    {
        $user = $this->userOuService->getActiveUser();
        $threadFilter->setAssignedUserId([$user->getId()]);
        return $this;
    }

    protected function filterByAssigned(ThreadFilter $threadFilter)
    {
        $threadFilter->setIsAssigned(true);
        return $this;
    }

    protected function filterByUnassigned(ThreadFilter $threadFilter)
    {
        $threadFilter->setIsAssigned(false);
        return $this;
    }

    /**
     * @return array
     */
    public function fetchThreadDataForId($id)
    {
        $thread = $this->threadService->fetch($id);
        return $this->formatThreadData($thread);
    }

    public function updateThreadAndReturnData($id, $assignedUserId = false, $status = null)
    {
        $thread = $this->threadService->fetch($id);

        $this->updateThreadAssignedUserId($thread, $assignedUserId)
            ->updateThreadStatus($thread, $status);

        try {
            $this->threadService->save($thread);
        } catch (NotModified $e) {
            // NoOp
        }
        return $this->formatThreadData($thread);
    }

    protected function updateThreadAssignedUserId(Thread $thread, $assignedUserId) {
        if (!$this->isAssignedUserIdProvided($assignedUserId)) {
            return $this;
        }
        if ($assignedUserId == static::ASSIGNEE_ACTIVE_USER) {
            $user = $this->userOuService->getActiveUser();
            $assignedUserId = $user->getId();
        // null, meaning unassign, can come through as ''
        } elseif ($assignedUserId == '') {
            $assignedUserId = null;
        }
        $thread->setAssignedUserId($assignedUserId);
        return $this;
    }

    protected function isAssignedUserIdProvided($assignedUserId)
    {
        // As null is a valid value (it means unassign) we default to false when its not specified at all
        return ($assignedUserId !== false);
    }

    protected function updateThreadStatus(Thread $thread, $status)
    {
        if (!$this->hasThreadStatusChanged($thread, $status)) {
            return $this;
        }
        if ($status == ThreadStatus::RESOLVED) {
            $this->threadResolveFactory->__invoke($thread);
        } else {
            $thread->setStatus($status);
        }
        return $this;
    }

    protected function hasThreadStatusChanged(Thread $thread, $status)
    {
        return ($status && $status != $thread->getStatus());
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

    protected function setUserService(UserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    protected  function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setThreadResolveFactory(ThreadResolveFactory $threadResolveFactory)
    {
        $this->threadResolveFactory = $threadResolveFactory;
        return $this;
    }
}
