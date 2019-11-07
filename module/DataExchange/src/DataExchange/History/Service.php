<?php
namespace DataExchange\History;

use CG\DataExchange\FileContents\StorageInterface as FileStorage;
use CG\DataExchangeHistory\Collection as Histories;
use CG\DataExchangeHistory\Entity as History;
use CG\DataExchangeHistory\Filter as HistoryFilter;
use CG\DataExchangeHistory\Service as HistoryService;
use CG\DataExchangeSchedule\Gearman\StopProcessingScheduleService;
use CG\ETag\Exception\Conflict;
use CG\Http\Exception\Exception3xx\NotModified;
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

    private const DEFAULT_FILE_EXTENSION = 'csv';

    private const MAX_SAVE_RETRIES = 3;

    private const LOG_CODE = 'DataExchangeHistoryService';
    private const LOG_MESSAGE_USER_NOT_FOUND =  'User with ID %s not found, even thought it\'s set on the History entity';

    /** @var HistoryService */
    protected $historyService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var UserService */
    protected $userService;
    /** @var FileStorage */
    protected $fileStorage;
    /** @var StopProcessingScheduleService */
    protected $stopProcessingScheduleService;

    public function __construct(
        HistoryService $historyService,
        ActiveUserInterface $activeUserContainer,
        UserService $userService,
        FileStorage $fileStorage,
        StopProcessingScheduleService $stopProcessingScheduleService
    ) {
        $this->historyService = $historyService;
        $this->activeUserContainer = $activeUserContainer;
        $this->userService = $userService;
        $this->fileStorage = $fileStorage;
        $this->stopProcessingScheduleService = $stopProcessingScheduleService;
    }

    public static function getAllowedFileTypes(): array
    {
        return [
            FileStorage::TYPE_FILE,
            FileStorage::TYPE_REPORT_FAILED,
            FileStorage::TYPE_REPORT_SUCCEEDED,
            FileStorage::TYPE_REPORT_UNPROCESSED
        ];
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

    public function fetchFile(int $historyId, string $fileType): array
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $fileContents = $this->fileStorage->fetch($rootOuId, $historyId, $fileType);
        $fileName = $this->buildFileName($fileType, $historyId);
        return [$fileName, $fileContents];
    }

    public function stopSchedule(int $historyId): bool
    {
        /** @var History $history */
        $history = $this->historyService->fetch($historyId);
        $this->stopProcessingScheduleService->stopProcessingSchedule($history->getJobId());
        return $this->removeJobIdFromHistory($history);
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
            $historiesArray[] = $this->formatHistoryAsArray($history);
        }

        return $historiesArray;
    }

    protected function formatHistoryAsArray(History $history): array
    {
        return array_merge(
            $history->toArray(),
            [
                'type' => $this->formatHistoryType($history),
                'user' => $this->formatUser($history),
                'endDate' => $this->formatEndDate($history)
            ],
            $this->buildFilesArray($history)
        );
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
            'unprocessedLink' => $this->getFileLinkForType($history, FileStorage::TYPE_REPORT_SUCCEEDED),
            'successfulLink' => $this->getFileLinkForType($history, FileStorage::TYPE_REPORT_SUCCEEDED),
            'failedLink' => $this->getFileLinkForType($history, FileStorage::TYPE_REPORT_FAILED),
            'fileLink' => $this->getFileLinkForType($history, FileStorage::TYPE_FILE)
        ];
    }

    protected function getFileLinkForType(History $history, string $type): ?string
    {
        if (!$this->fileStorage->exists($history->getOrganisationUnitId(), $history->getId(), $type)) {
            return null;
        }

        return '/dataExchange/history/files/' . $history->getId() . '/' . $type;
    }

    protected function buildFileName(string $type, int $historyId): string
    {
        return $type . '_' . $historyId . '.' . static::DEFAULT_FILE_EXTENSION;
    }

    protected function removeJobIdFromHistory(History $history): bool
    {
        for ($retry = 0; $retry < static::MAX_SAVE_RETRIES; $retry++) {
            try {
                $history->setJobId(null);
                $this->historyService->save($history);
                return true;
            } catch (NotModified $e) {
                return true;
            } catch (Conflict $exception) {
                $history = $this->historyService->fetch($history->getId());
            }
        }

        return false;
    }
}
