<?php
namespace Settings\CreateListings\Csv;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Listing\Gearman\Workload\CreateListingsFromImport as Workload;
use CG\User\ActiveUserInterface;
use GearmanClient;
use CG\Listing\Csv\Entity;
use CG\Listing\Csv\Mapper;
use CG\Listing\Csv\StorageInterface;
use function CG\Stdlib\hyphenToCamelCase;

class Importer
{
    const JOB_QUEUE_NAME = '%sCreateListingsFromImport';

    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var StorageInterface*/
    protected $storage;
    /** @var Mapper */
    protected $mapper;
    /** @var GearmanClient */
    protected $gearmanClient;
    /** @var AccountService */
    protected $accountService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        StorageInterface $storage,
        Mapper $mapper,
        GearmanClient $gearmanClient,
        AccountService $accountService
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->storage = $storage;
        $this->mapper = $mapper;
        $this->gearmanClient = $gearmanClient;
        $this->accountService = $accountService;
    }

    public function importFromCsv(string $fileContents, int $accountId)
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $entity = $this->saveCsv($fileContents, $accountId, $rootOuId);
        $this->createImportJob($entity);
    }

    protected function saveCsv(string $fileContents, int $accountId, int $rootOuId): Entity
    {
        $entity = $this->mapper->fromFileAndMetadata($fileContents, ['accountId' => $accountId, 'rootOuId' => $rootOuId]);
        return $this->storage->save($entity);
    }

    protected function createImportJob(Entity $entity)
    {
        $queueName = $this->getJobQueueName($entity);
        $workload = new Workload($entity->getAccountId(), $entity->getRootOuId(), $entity->getId());
        $handle = $queueName . '-' . $entity->getId();
        $this->gearmanClient->doBackground($queueName, serialize($workload), $handle);
    }

    protected function getJobQueueName(Entity $entity): string
    {
        /** @var Account $account */
        $account = $this->accountService->fetch($entity->getAccountId());
        return sprintf(static::JOB_QUEUE_NAME, hyphenToCamelCase($account->getChannel()));
    }
}