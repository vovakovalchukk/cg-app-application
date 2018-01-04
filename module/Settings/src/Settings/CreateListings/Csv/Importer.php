<?php
namespace Settings\CreateListings\Csv;

use CG\Listing\Gearman\Workload\CreateListingsFromImport as Workload;
use CG\User\ActiveUserInterface;
use GearmanClient;
use Settings\CreateListings\Csv\Entity;
use Settings\CreateListings\Csv\Mapper;
use Settings\CreateListings\Csv\StorageInterface;

class Importer
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var StorageInterface*/
    protected $storage;
    /** @var Mapper */
    protected $mapper;
    /** @var GearmanClient */
    protected $gearmanClient;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        StorageInterface $storage,
        Mapper $mapper,
        GearmanClient $gearmanClient
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->storage = $storage;
        $this->mapper = $mapper;
        $this->gearmanClient = $gearmanClient;
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
        $workload = new Workload($entity->getAccountId(), $entity->getRootOuId());
        $this->gearmanClient->doBackground(Workload::FUNCTION_NAME, serialize($workload));
    }
}