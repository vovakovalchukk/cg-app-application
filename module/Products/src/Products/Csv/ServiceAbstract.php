<?php
namespace Products\Csv;

use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\User\ActiveUserInterface;
use Products\Csv\Stock\ProgressStorage;
use \SplFileObject as Csv;
use League\Csv\Writer as CsvWriter;

abstract class ServiceAbstract
{
    const MIME_TYPE = "text/csv";
    const FILENAME = "";
    const COLLECTION_SIZE = 200;
    const EVENT_TYPE = "";
    const EVENT_IMPORTED = "Imported";
    const EVENT_EXPORTED = "Exported";

    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var ProgressStorageAbstract */
    protected $progressStorage;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        IntercomEventService $intercomEventService,
        ProgressStorageAbstract $progressStorage
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->intercomEventService = $intercomEventService;
        $this->progressStorage = $progressStorage;
    }

    public function uploadCsvForActiveUser($updateOption, $fileContents)
    {
        $this->uploadCsv(
            $this->getActiveUserId(),
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            $updateOption,
            $fileContents
        );
    }

    public function generateCsvForActiveUser($progressKey = null): CsvWriter
    {
        return $this->generateCsv(
            $this->getActiveUserId(),
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            $progressKey
        );
    }

    public function checkProgress($progressKey)
    {
        $count = $this->progressStorage->getProgress($progressKey);
        if ($count === null) {
            return null;
        }
        return (int)$count;
    }

    public function getTotalForProgress($key)
    {
        $total = $this->progressStorage->getTotal($key);
        if ($total === null) {
            return null;
        }
        return (int)$total;
    }

    public function startProgress($progressKey)
    {
        $this->progressStorage->setProgress($progressKey, 0);
    }

    protected function notifyIntercom($event, $userId)
    {
        $this->intercomEventService->save(
            new IntercomEvent($event, $userId)
        );
    }

    protected function getActiveUserId()
    {
        return $this->activeUserContainer->getActiveUser()->getId();
    }

    protected function endProgress($progressKey)
    {
        $this->progressStorage->removeProgress($progressKey);
    }

    abstract protected function generateCsv($userId, $organisationUnitId, $progressKey = null): CsvWriter;

    abstract protected function getHeaders(): array;
}
