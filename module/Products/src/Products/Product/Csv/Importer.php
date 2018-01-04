<?php
namespace Products\Product\Csv;

use CG\User\ActiveUserInterface;
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

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        StorageInterface $storage,
        Mapper $mapper
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->storage = $storage;
        $this->mapper = $mapper;
    }

    public function importFromCsv(string $fileContents, int $accountId)
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $this->saveCsv($fileContents, $accountId, $rootOuId);
        // TODO: actually do the import by creating a gearman job
    }

    protected function saveCsv(string $fileContents, int $accountId, int $rootOuId): Entity
    {
        $entity = $this->mapper->fromFileAndMetadata($fileContents, ['accountId' => $accountId, 'rootOuId' => $rootOuId]);
        return $this->storage->save($entity);
    }
}