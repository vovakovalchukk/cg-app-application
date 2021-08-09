<?php
namespace Messages\Thread;

use CG\Communication\Message\Attachment\Collection as AttachmentCollection;
use CG\Communication\Message\Entity as Message;
use CG\Communication\Thread\Collection as ThreadCollection;
use CG\Communication\Thread\Entity as Thread;
use CG\Communication\Thread\Filter as ThreadFilter;
use CG\Communication\Thread\ResolveFactory as ThreadResolveFactory;
use CG\Communication\Thread\Service as ThreadService;
use CG\Communication\Thread\Status as ThreadStatus;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Order\Shared\CustomerCounts\Service as CustomerCountService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOuService;
use CG\User\Service as UserService;
use CG_UI\View\Helper\DateFormat;
use Messages\Message\Attachment\Service as AttachmentService;
use Messages\Message\FormatMessageDataTrait;
use Messages\Thread\Formatter\Service as FormatterService;
use Messages\Thread\OrdersInformation\Factory as OrdersInformationFactory;
use Predis\Client as PredisClient;
use Zend\Navigation\Page\AbstractPage as NavPage;
use Zend\View\Helper\Url;

class Service implements LoggerAwareInterface
{
    use LogTrait;
    use FormatMessageDataTrait;

    protected const DEFAULT_LIMIT = 100;
    protected const KEY_HAS_NEW = 'messages-has-new-user:';
    protected const TTL_HAS_NEW = 300;
    protected const ASSIGNEE_ACTIVE_USER = 'active-user';
    protected const ASSIGNEE_ASSIGNED = 'assigned';
    protected const ASSIGNEE_UNASSIGNED = 'unassigned';
    protected const EVENT_THREAD_RESOLVED = 'Message Thread Resolved';
    protected const LOG_CODE = 'MessageThreadService';
    protected const LOG_MATCH_THREAD_TO_USER = 'Failed matching user with id %s to thread';

    /** @var ThreadService $threadService */
    protected $threadService;
    /** @var UserOuService $userOuService */
    protected $userOuService;
    /** @var UserService $userService */
    protected $userService;
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
    /** @var AttachmentService */
    protected $attachmentService;
    /** @var OrdersInformationFactory */
    protected $ordersInformationFactory;
    /** @var FormatterService */
    protected $formatterService;
    /** @var array */
    protected $threadsUsers = [];

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
        CustomerCountService $customerCountService,
        ThreadResolveFactory $threadResolveFactory,
        IntercomEventService $intercomEventService,
        DateFormat $dateFormatter,
        Url $url,
        AttachmentService $attachmentService,
        OrdersInformationFactory $ordersInformationFactory,
        FormatterService $formatterService
    ) {
        $this->threadService = $threadService;
        $this->userOuService = $userOuService;
        $this->userService = $userService;
        $this->customerCountService = $customerCountService;
        $this->threadResolveFactory = $threadResolveFactory;
        $this->intercomEventService = $intercomEventService;
        $this->dateFormatter = $dateFormatter;
        $this->url = $url;
        $this->attachmentService = $attachmentService;
        $this->ordersInformationFactory = $ordersInformationFactory;
        $this->formatterService = $formatterService;
    }

    public function fetchThreadDataForFilters(array $filters, ?int $page = 1, bool $sortDescending = true): array
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
            $attachments = $this->attachmentService->fetchAttachmentsForThreads($threads);
            return $this->convertThreadCollectionToArray($sortedThreads, $attachments);
        } catch (Notfound $e) {
            return [];
        }
    }

    protected function filterByAssignee(ThreadFilter $threadFilter, string $assignee): Service
    {
        $assignee = strtolower($assignee);
        if (!isset($this->assigneeMethodMap[$assignee])) {
            throw new \UnexpectedValueException(__METHOD__.' was passed unhandled assignee "' . $assignee . '"');
        }
        $method = $this->assigneeMethodMap[$assignee];
        $this->$method($threadFilter);
        return $this;
    }

    protected function filterByNotResolved(ThreadFilter $threadFilter): Service
    {
        $otherStatuses = array_diff(ThreadStatus::getStatuses(), [ThreadStatus::RESOLVED]);
        $threadFilter->setStatus($otherStatuses);
        return $this;
    }

    protected function convertThreadCollectionToArray(
        ThreadCollection $threads,
        AttachmentCollection $attachments
    ): array {
        $threadsDataOverrides = $this->formatterService->formatThreadsDataOverrides($threads);
        $threadsData = [];
        /** @var Thread $thread */
        foreach ($threads as $thread) {
            $threadsData[] = $this->formatThreadData(
                $thread,
                $attachments,
                $this->getOverridesForThread($thread, $threadsDataOverrides)
            );
        }
        return $threadsData;
    }

    protected function getOverridesForThread(Thread $thread, array $threadsDataOverrides): array
    {
        $overridesForChannel = $threadsDataOverrides[$thread->getChannel()] ?? [];
        return $overridesForChannel[$thread->getId()] ?? [];
    }

    protected function formatThreadData(
        Thread $thread,
        AttachmentCollection $attachments,
        array $overrides,
        bool $includeCounts = false
    ): array {
        $threadData = $thread->toArray();
        $threadData['messages'] = $this->formatMessagesData($thread, $attachments);

        $ordersInformation = $this->ordersInformationFactory->fromThread($thread);
        $threadData['accountName'] = $ordersInformation->getAccountName();

        $dateFormatter = $this->dateFormatter;
        $threadData['createdFuzzy'] = (new StdlibDateTime($threadData['created']))->fuzzyFormat();
        $threadData['created'] = $dateFormatter($threadData['created']);
        $threadData['updatedFuzzy'] = (new StdlibDateTime($threadData['updated']))->fuzzyFormat();
        $threadData['updated'] = $dateFormatter($threadData['updated']);
        $threadData['ordersLink'] = $ordersInformation->getOrdersUrl();
        $threadData['ordersLinkText'] = $ordersInformation->getLinkText();

        $threadData['ordersCount'] = '?';
        if ($includeCounts) {
            $threadData['ordersCount'] = $ordersInformation->getCount();
        }

        $threadData['assignedUserName'] = $this->setThreadAssignedUserName($threadData['assignedUserId']);

        return array_merge($threadData, $overrides);
    }

    protected function getThreadAssignedUserName(int $userId): string
    {
        if (!$assignedUserId) {
            return '';
        }

        if ($name = $this->getCachedThreadUser($userId)) {
            return $name;
        }

        try {
            $user = $this->userService->fetch($userId);
            $name =  $user->getFirstName() . ' ' . $user->getLastName();
            $this->cacheThreadUser($userId, $name);
        } catch (NotFound $e) {
            $this->logDebug(static::LOG_MATCH_THREAD_TO_USER, [$userId], static::LOG_CODE);

            return '';
        }
    }

    protected function cacheThreadUser($userId, $userName): void
    {
        $this->threadsUsers[$userId] = $userName;
    }

    protected function getCachedThreadUser($userId): ?string
    {
        if (isset($this->threadsUsers[$userId])) {
            return $this->threadsUsers[$userId];
        }

        return null;
    }

    protected function formatMessagesData(Thread $thread, AttachmentCollection $attachments): array
    {
        $messages = [];
        /** @var Message $message */
        foreach ($thread->getMessages() as $message) {
            /** @var AttachmentCollection $attachmentsForMessage */
            $attachmentsForMessage = $attachments->getBy('messageId', $message->getId());
            $messageData = $this->formatMessageData($message, $thread, $attachmentsForMessage);
            $messages[$message->getCreated()] = $messageData;
        }
        ksort($messages);
        return array_values($messages);
    }

    public function getOrdersInformationForId(string $threadId): OrdersInformation
    {
        return $this->ordersInformationFactory->fromThread($this->threadService->fetch($threadId), true);
    }

    protected function sortThreadCollection(ThreadCollection $threads): ThreadCollection
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

    protected function filterByActiveUser(ThreadFilter $threadFilter): Service
    {
        $user = $this->userOuService->getActiveUser();
        $threadFilter->setAssignedUserId([$user->getId()]);
        return $this;
    }

    protected function filterByAssigned(ThreadFilter $threadFilter): Service
    {
        $threadFilter->setIsAssigned(true);
        return $this;
    }

    protected function filterByUnassigned(ThreadFilter $threadFilter): Service
    {
        $threadFilter->setIsAssigned(false);
        return $this;
    }

    public function fetchThreadDataForId(string $id): array
    {
        /** @var Thread $thread */
        $thread = $this->threadService->fetch($id);
        $threadCollection = new ThreadCollection(Thread::class, __FUNCTION__);
        $threadCollection->attach($thread);

        return $this->formatThreadData(
            $thread,
            $this->attachmentService->fetchAttachmentsForThreads($threadCollection),
            $this->formatterService->formatThreadsDataOverrides($threadCollection)
        );
    }

    public function updateThreadAndReturnData(string $id, $assignedUserId = false, ?string $status = null): array
    {
        $thread = $this->threadService->fetch($id);
        $threadCollection = new ThreadCollection(Thread::class, __FUNCTION__);
        $threadCollection->attach($thread);

        $this->updateThreadAssignedUserId($thread, $assignedUserId)
            ->updateThreadStatus($thread, $status);

        try {
            $this->threadService->save($thread);
        } catch (NotModified $e) {
            // NoOp
        }

        return $this->formatThreadData(
            $thread,
            $this->attachmentService->fetchAttachmentsForThreads($threadCollection),
            $this->formatterService->formatThreadsDataOverrides($threadCollection)
        );
    }

    protected function updateThreadAssignedUserId(Thread $thread, $assignedUserId): Service
    {
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

    protected function isAssignedUserIdProvided($assignedUserId): bool
    {
        // As null is a valid value (it means unassign) we default to false when its not specified at all
        return ($assignedUserId !== false);
    }

    protected function updateThreadStatus(Thread $thread, ?string $status): Service
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

    protected function hasThreadStatusChanged(Thread $thread, ?string $status): bool
    {
        return ($status && $status != $thread->getStatus());
    }

    protected function notifyOfResolve(): void
    {
        $user = $this->userOuService->getActiveUser();
        $event = new IntercomEvent(static::EVENT_THREAD_RESOLVED, $user->getId());
        $this->intercomEventService->save($event);
    }

    public function hasNew(): bool
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

    protected function hasNewUnassigned(OrganisationUnit $ou): bool
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

    protected function hasNewAssignedToActiveUser(OrganisationUnit $ou): bool
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

    public function changeNavSpriteIfHasNew(NavPage $page): void
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

    // Required by FormatMessageDataTrait
    protected function getDateFormatter()
    {
        return $this->dateFormatter;
    }
}
