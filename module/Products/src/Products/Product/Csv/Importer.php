<?php
namespace Products\Product\Csv;

use CG\Product\Gearman\Workload\ProductImport as Workload;
use CG\User\ActiveUserInterface;
use GearmanClient;
use Products\Product\Csv\Entity;
use Products\Product\Csv\Mapper;
use Products\Product\Csv\StorageInterface;

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
        $this->saveCsv($fileContents, $accountId, $rootOuId);
        $this->createImportJob($accountId, $rootOuId);
    }

    protected function saveCsv(string $fileContents, int $accountId, int $rootOuId): Entity
    {
        $entity = $this->mapper->fromFileAndMetadata($fileContents, ['accountId' => $accountId, 'rootOuId' => $rootOuId]);
        return $this->storage->save($entity);
    }

    protected function createImportJob(int $accountId, int $rootOuId)
    {
        $workload = new Workload($accountId, $rootOuId);
        $this->gearmanClient->doBackground(Workload::FUNCTION_NAME, serialize($workload));
    }
}