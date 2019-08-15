<?php
namespace Messages\Thread;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Communication\Thread\Collection as ThreadCollection;
use CG\Communication\Thread\Entity as Thread;
use CG\Communication\Thread\Filter as ThreadFilter;
use CG\Communication\Thread\ResolveFactory as ThreadResolveFactory;
use CG\Communication\Thread\Service as ThreadService;
use CG\Communication\Thread\Status as ThreadStatus;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\CustomerCounts\Service as CustomerCountService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\OrganisationUnit\Service as UserOuService;
use CG\User\Service as UserService;
use CG_UI\View\Helper\DateFormat;
use Messages\Message\FormatMessageDataTrait;
use Orders\Module as OrdersModule;
use Zend\Navigation\Page\AbstractPage as NavPage;
use Zend\View\Helper\Url;

class Service
{
    use FormatMessageDataTrait;

    const DEFAULT_LIMIT = 100;
    const KEY_HAS_NEW = 'messages-has-new-user:';
    const TTL_HAS_NEW = 300;
    const ASSIGNEE_ACTIVE_USER = 'active-user';
    const ASSIGNEE_ASSIGNED = 'assigned';
    const ASSIGNEE_UNASSIGNED = 'unassigned';
    const EVENT_THREAD_RESOLVED = 'Message Thread Resolved';

    /** @var ThreadService $threadService */
    protected $threadService;
    /** @var UserOuService $userOuService */
    protected $userOuService;
    /** @var UserService $userService */
    protected $userService;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var CustomerCountService $customerCountService */
    protected $customerCountService;
    /** @var ThreadResolveFactory $threadResolveFactory */
    protected $threadResolveFactory;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var DateFormat $dateFormatter */
    protected $dateFormatter;
    /** @var Url $url */
    protected $url;

    protected $assigneeMethodMap = [
        self::ASSIGNEE_ACTIVE_USER => 'filterByActiveUser',
        self::ASSIGNEE_ASSIGNED => 'filterByAssigned',
        self::ASSIGNEE_UNASSIGNED => 'filterByUnassigned',
    ];
    protected $filtersThatIncludeResolved = [
        'status' => 'status',
        'searchTerm' => 'searchTerm',
        'id' => 'id',
        'externalUsername' => 'externalUsername'
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
        CustomerCountService $customerCountService,
        ThreadResolveFactory $threadResolveFactory,
        IntercomEventService $intercomEventService,
        DateFormat $dateFormatter,
        Url $url
    ) {
        $this
            ->setThreadService($threadService)
            ->setUserOuService($userOuService)
            ->setUserService($userService)
            ->setAccountService($accountService)
            ->setCustomerCountService($customerCountService)
            ->setThreadResolveFactory($threadResolveFactory)
            ->setIntercomEventService($intercomEventService)
            ->setDateFormatter($dateFormatter)
            ->setUrl($url);
    }

    /**
     * @return array
     */
    public function fetchThreadDataForFilters(array $filters, $page = 1, $sortDescending = true)
    {
        $ou = $this->userOuService->getRootOuByActiveUser();

        $threadFilter = new ThreadFilter();
        $threadFilter->setLimit(isset($filters['limit']) ? $filters['limit'] : static::DEFAULT_LIMIT)
            ->setOrganisationUnitId([$ou->getId()])
            ->setPage($page)
            ->setSortBy(ThreadFilter::SORT_UPDATED)
            ->setSortDirection($sortDescending ? ThreadFilter::SORT_DESCENDING : ThreadFilter::SORT_ASCENDING);
        if (isset($filters['id'])) {
            $threadFilter->setId((array)$filters['id']);
        }
        if (isset($filters['externalUsername'])) {
            $threadFilter->setExternalUsername((array)$filters['externalUsername']);
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

    protected function formatThreadData(Thread $thread, $includeCounts = false)
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
        $externalUsername = $this->attemptToRemoveAdditionalDataFromExternalUsername($thread, $account);

        $dateFormatter = $this->dateFormatter;
        $threadData['createdFuzzy'] = (new StdlibDateTime($threadData['created']))->fuzzyFormat();
        $threadData['created'] = $dateFormatter($threadData['created']);
        $threadData['updatedFuzzy'] = (new StdlibDateTime($threadData['updated']))->fuzzyFormat();
        $threadData['updated'] = $dateFormatter($threadData['updated']);
        $threadData['ordersLink'] = call_user_func(
            $this->url,
            OrdersModule::ROUTE,
            [],
            [
                'query' => ['search' => $externalUsername, 'searchField' => ['order.externalUsername']]
            ]
        );

        $threadData['ordersCount'] = '?';
        if ($includeCounts) {
            $threadData['ordersCount'] = $this->getOrderCount($thread);
        }

        $threadData['assignedUserName'] = '';
        if ($threadData['assignedUserId']) {
            $assignedUser = $this->userService->fetch($threadData['assignedUserId']);
            $threadData['assignedUserName'] = $assignedUser->getFirstName() . ' ' . $assignedUser->getLastName();
        }

        return $threadData;
    }

    public function getOrderCountForId($id)
    {
        $thread = $this->threadService->fetch($id);
        return $this->getOrderCount($thread);
    }

    protected function getOrderCount(Thread $thread)
    {
        $account = $this->accountService->fetch($thread->getAccountId());
        $externalUsername = $this->attemptToRemoveAdditionalDataFromExternalUsername($thread, $account);
        return $this->customerCountService->fetch($thread->getOrganisationUnitId(), $externalUsername);
    }

    protected function sortThreadCollection(ThreadCollection $threads)
    {
        // Sort by status
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
            $threadsByStatus[$thread->getStatus()][] = $thread;
        }
        foreach ($threadsByStatus as $status => $threadsByUpdated) {
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
            $this->notifyOfResolve();
        } else {
            $thread->setStatus($status);
        }
        return $this;
    }

    protected function hasThreadStatusChanged(Thread $thread, $status)
    {
        return ($status && $status != $thread->getStatus());
    }

    protected function notifyOfResolve()
    {
        $user = $this->userOuService->getActiveUser();
        $event = new IntercomEvent(static::EVENT_THREAD_RESOLVED, $user->getId());
        $this->intercomEventService->save($event);
    }

    /**
     * @return bool
     */
    public function hasNew()
    {
        $success = false;
        $user = $this->userOuService->getActiveUser();
        $cacheKey = static::KEY_HAS_NEW . $user->getId();
        $cachedValue = apc_fetch($cacheKey, $success);
        if ($success) {
            return $cachedValue;
        }

        $ou = $this->userOuService->getRootOuByActiveUser();
        $hasNew = ($this->hasNewUnassigned($ou) || $this->hasNewAssignedToActiveUser($ou));
        apc_store($cacheKey, $hasNew, static::TTL_HAS_NEW);
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
        try {
            if (!$this->userOuService->getActiveUser() || !$this->hasNew()) {
                return;
            }
            $page->set('sprite', 'sprite-messages-alert-18-white');
        } catch (\Exception $e) {
            // No-op, don't stop rendering the nav just for this
        }
    }

    /**
     * @return self
     */
    protected function setThreadService(ThreadService $threadService)
    {
        $this->threadService = $threadService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setUserOuService(UserOuService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setUserService(UserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    /**
     * @return self
     */
    protected  function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setCustomerCountService(CustomerCountService $customerCountService)
    {
        $this->customerCountService = $customerCountService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setThreadResolveFactory(ThreadResolveFactory $threadResolveFactory)
    {
        $this->threadResolveFactory = $threadResolveFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setDateFormatter(DateFormat $dateFormatter)
    {
        $this->dateFormatter = $dateFormatter;
        return $this;
    }

    // Required by FormatMessageDataTrait
    protected function getDateFormatter()
    {
        return $this->dateFormatter;
    }

    /**
     * @return self
     */
    protected function setUrl(Url $url)
    {
        $this->url = $url;
        return $this;
    }

    protected function attemptToRemoveAdditionalDataFromExternalUsername(Thread $thread, Account $account): string
    {
        $externalUsername = $thread->getExternalUsername();

        if ($account->getChannel() !== 'amazon') {
            return $externalUsername;
        }

        $externalUsername = preg_replace('/(\+[A-z0-9]+)/', '', $externalUsername);

        return $externalUsername;
    }
}
