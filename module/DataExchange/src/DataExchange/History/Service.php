<?php
namespace DataExchange\History\Service;

use CG\DataExchangeHistory\Collection as Histories;
use CG\DataExchangeHistory\Entity as History;
use CG\DataExchangeHistory\Filter as HistoryFilter;
use CG\DataExchangeHistory\Service as HistoryService;
use CG\Stdlib\DateTime as CgDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\User\Service as UserService;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    public const DEFAULT_LIMIT = 50;
    public const DEFAULT_PAGE = 1;

    private const DEFAULT_SORT_BY_FIELD = 'startDate';
    private const DEFAULT_SORT_BY_DIRECTION = 'DESC';
    private const END_DATE_ENDED_BY_USER = 'Ended by User';
    private const END_DATE_IN_PROGRESS = 'In Progress';

    private const LOG_CODE = 'DataExchangeHistoryService';
    private const LOG_MESSAGE_USER_NOT_FOUND =  'User with ID %s not found, even thought it\'s set on the History entity';

    /** @var HistoryService */
    protected $historyService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var UserService */
    protected $userService;

    public function __construct(
        HistoryService $historyService,
        ActiveUserInterface $activeUserContainer,
        UserService $userService
    ) {
        $this->historyService = $historyService;
        $this->activeUserContainer = $activeUserContainer;
        $this->userService = $userService;
    }

    public function fetchForActiveUser(int $limit = self::DEFAULT_LIMIT, int $page = self::DEFAULT_PAGE): array
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildFilter($limit, $page, $rootOuId);
            /** @var Histories $histories */
            $histories = $this->historyService->fetchCollectionByFilter($filter);
            return $this->formatHistoriesAsArray($histories);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function buildFilter(int $limit, int $page, int $ouId): HistoryFilter
    {
        return new HistoryFilter(
            $limit,
            $page,
            [],
            [$ouId],
            [],
            [],
            [],
            [],
            null,
            null,
            null,
            null,
            [],
            static::DEFAULT_SORT_BY_FIELD,
            static::DEFAULT_SORT_BY_DIRECTION
        );
    }

    protected function formatHistoriesAsArray(Histories $histories): array
    {
        $historiesArray = [];
        /** @var History $history */
        foreach ($histories as $history) {
            $historyData = $history->toArray();
            $historiesArray = array_merge(
                $historyData,
                [
                    'type' => $this->formatHistoryType($history),
                    'user' => $this->formatUser($history),
                    'endDate' => $this->formatEndDate($history)
                ],
                $this->buildFilesArray($history)
            );
        }

        return $historiesArray;
    }

    protected function formatHistoryType(History $history): string
    {
        return ucfirst($history->getType()) . ' ' . ucfirst($history->getOperation());
    }

    protected function formatUser(History $history): ?string
    {
        if ($history->getUserId() === null) {
            return null;
        }

        try {
            /** @var User $user */
            $user = $this->userService->fetch($history->getUserId());
            return $user->getFirstName() . ' ' . $user->getLastName();
        } catch (NotFound $e) {
            $this->logWarningException($e, static::LOG_MESSAGE_USER_NOT_FOUND, [$history->getUserId()], [static::LOG_CODE]);
            return null;
        }
    }

    protected function formatEndDate(History $history): string
    {
        if ($history->getEndDate() instanceof CgDateTime) {
            return $history->getEndDate()->format(CgDateTime::FORMAT);
        }

        if ($history->getJobId() === null) {
            return static::END_DATE_ENDED_BY_USER;
        }

        return static::END_DATE_IN_PROGRESS;
    }

    protected function buildFilesArray(History $history): array
    {
        return [
            'unprocessedLink' => null,
            'successfulLink' => null,
            'failedLink' => null,
            'fileLink' => null
        ];
    }
}
