<?php
namespace Messages\Thread;

use CG\Account\Client\Service as AccountService;
use CG\Communication\Message\Entity as Message;
use CG\Communication\Thread\Collection as ThreadCollection;
use CG\Communication\Thread\Entity as Thread;
use CG\Communication\Thread\Filter as ThreadFilter;
use CG\Communication\Thread\Service as ThreadService;
use CG\Communication\Thread\Status as ThreadStatus;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\User\OrganisationUnit\Service as UserOuService;
use CG\User\Service as UserService;

class Service
{
    const DEFAULT_LIMIT = 50;
    const ASSIGNEE_ACTIVE_USER = 'active-user';
    const ASSIGNEE_ASSIGNED = 'assigned';
    const ASSIGNEE_UNASSIGNED = 'unassigned';

    protected $threadService;
    protected $userOuService;
    protected $userService;
    protected $accountService;

    protected $assigneeMethodMap = [
        self::ASSIGNEE_ACTIVE_USER => 'filterByActiveUser',
        self::ASSIGNEE_ASSIGNED => 'filterByAssigned',
        self::ASSIGNEE_UNASSIGNED => 'filterByUnassigned',
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
        AccountService $accountService
    ) {
        $this->setThreadService($threadService)
            ->setUserOuService($userOuService)
            ->setUserService($userService)
            ->setAccountService($accountService);
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
        if (isset($filters['searchTerm'])) {
            $threadFilter->setSearchTerm($filters['searchTerm']);
        }
        if (!isset($filters['status']) && !isset($filters['searchTerm'])) {
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
            $messageData = $this->formatMessageData($message);
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

    protected function formatMessageData(Message $message)
    {
        $messageData = $message->toArray();
        $created = new StdlibDateTime($messageData['created']);
        $messageData['created'] = $created->uiFormat();
        $messageData['createdFuzzy'] = $created->fuzzyFormat();
        return $messageData;
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

    public function fetchThreadDataForId($id)
    {
        $thread = $this->threadService->fetch($id);
        return $this->formatThreadData($thread);
    }

    public function updateThreadAndReturnData($id, $assignedUserId = null, $status = null)
    {
        $thread = $this->threadService->fetch($id);
        // null is a valid assignee
        if ($assignedUserId !== false) {
            if ($assignedUserId == static::ASSIGNEE_ACTIVE_USER) {
                $user = $this->userOuService->getActiveUser();
                $assignedUserId = $user->getId();
            // null can come through as ''
            } elseif ($assignedUserId == '') {
                $assignedUserId = null;
            }
            $thread->setAssignedUserId($assignedUserId);
        }
        if ($status) {
// TODO: if $status == 'resolved' then notify channel. Requires CGIV-4698
            $thread->setStatus($status);
        }
        try {
            $this->threadService->save($thread);
        } catch (NotModified $e) {
            // NoOp
        }
        return $this->formatThreadData($thread);
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
}
